<?php

declare(strict_types=1);

namespace SaturIo\DuckDB\Result;

use SaturIo\DuckDB\Exception\BigNumbersNotSupportedException;
use SaturIo\DuckDB\Exception\InvalidTimeException;
use SaturIo\DuckDB\FFI\CDataInterface;
use SaturIo\DuckDB\FFI\DuckDB as FFIDuckDB;

class ResultSet
{
    use ValidityTrait;

    public CDataInterface $currentChunk;

    public function __construct(
        public readonly FFIDuckDB $ffi,
        public readonly CDataInterface $result,
    ) {
        $this->currentChunk = $this->ffi->new('duckdb_data_chunk');
    }

    public function fetchChunk(): ?DataChunk
    {
        $newChunk = $this->ffi->fetchChunkToCDataInterface($this->result, $this->currentChunk);

        return $newChunk ? new DataChunk(
            $this->ffi,
            $this->currentChunk,
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
     * @throws \DateMalformedStringException
     * @throws InvalidTimeException
     */
    public function rows(bool $columnNameAsKey = false): iterable
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
        array_map(function ($row) use ($mask) {printf($mask, ...$row); }, iterator_to_array($this->rows()));
        echo $hyphenLine.PHP_EOL.PHP_EOL;
    }

    public function __destruct()
    {
        $this->ffi->destroyResult($this->ffi->addr($this->result));
    }
}
