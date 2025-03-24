<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

use DateMalformedStringException;
use Iterator;
use Saturio\DuckDB\Exception\BigNumbersNotSupportedException;
use Saturio\DuckDB\Exception\InvalidTimeException;
use Saturio\DuckDB\Exception\UnsupportedTypeException;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class ResultSet
{
    use ValidityTrait;
    use CollectMetrics;

    public function __construct(
        public readonly FFIDuckDB $ffi,
        public readonly NativeCData $result,
    ) {
        $this->initCollectMetrics();
    }

    public function fetchChunk(): ?DataChunk
    {
        $this->collectMetrics && collect_time($_, 'fetchChunk');
        $newChunk = $this->ffi->fetchChunk($this->result);

        return $newChunk ? new DataChunk(
            $this->ffi,
            $newChunk,
            reusable: false,
        ) : null;
    }

    public function chunks(): iterable
    {
        while ($chunk = $this->fetchChunk()) {
            yield $chunk;
        }
    }

    /**
     * @throws BigNumbersNotSupportedException
     * @throws DateMalformedStringException
     * @throws InvalidTimeException|UnsupportedTypeException
     */
    public function rows(bool $columnNameAsKey = false): Iterator
    {
        /** @var DataChunk $chunk */
        foreach ($this->chunks() as $chunk) {
            $rowCount = $chunk->rowCount();
            $columnCount = $chunk->columnCount();
            $dataGenerators = [];

            for ($columnIndex = 0; $columnIndex < $columnCount; ++$columnIndex) {
                $column = $chunk->getVector($columnIndex, rows: $rowCount);
                $dataGenerators[] = $column->getDataGenerator();
            }
            for ($rowIndex = 0; $rowIndex < $rowCount; ++$rowIndex) {
                foreach ($dataGenerators as $id => $dataGenerator) {
                    $rowData[$columnNameAsKey ? $this->columnName($id) : $id] = $dataGenerator->current();
                    $dataGenerator->next();
                }
                yield $rowData ?? null;
            }
            foreach ($dataGenerators as $id => $dataGenerator) {
                unset($dataGenerators[$id]);
            }

            $chunk->destroy();
        }
    }

    /**
     * @throws DateMalformedStringException
     * @throws UnsupportedTypeException
     * @throws BigNumbersNotSupportedException
     * @throws InvalidTimeException
     */
    public function vectorChunk(): Iterator
    {
        /** @var DataChunk $chunk */
        foreach ($this->chunks() as $chunk) {
            $rowCount = $chunk->rowCount();
            static $columnCount = $chunk->columnCount();

            $rows = [];
            for ($columnIndex = 0; $columnIndex < $columnCount; ++$columnIndex) {
                $rows[] = $chunk
                    ->getVector($columnIndex, rows: $rowCount)
                    ->getBatchRows();
            }
            yield $rows;
            $chunk->destroy();
        }
    }

    public function columnName($columnIndex): ?string
    {
        return $this->ffi->columnName($this->ffi->addr($this->result), $columnIndex);
    }

    public function columnCount(): int
    {
        return $this->ffi->columnCount($this->ffi->addr($this->result));
    }

    public function columnNames(): iterable
    {
        for ($columnIndex = 0; $columnIndex < $this->columnCount(); ++$columnIndex) {
            yield $columnIndex => $this->columnName($columnIndex);
        }
    }

    public function print(): void
    {
        $mask = '|'.implode(' |', array_fill(0, $this->columnCount(), ' %-15.15s ')).'|'.PHP_EOL;
        $hyphenLine = implode(array_fill(0, $this->columnCount() * 19, '-')).PHP_EOL;
        $bold = "\033[1;30m%s\033[0m";

        // Header - columns
        printf($bold, $hyphenLine);
        printf(sprintf($bold, $mask), ...iterator_to_array($this->columnNames()));
        printf($bold, $hyphenLine);

        // Body - rows
        $rows = $this->rows();
        iterator_apply($rows, function ($rows) use ($mask) {
            printf($mask, ...$rows->current());

            return true;
        }, [$rows]);
        echo $hyphenLine.PHP_EOL.PHP_EOL;
    }

    public function __destruct()
    {
        $this->ffi->destroyResult($this->ffi->addr($this->result));
    }
}
