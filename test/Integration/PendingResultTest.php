<?php

declare(strict_types=1);

namespace Integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\DuckDB;
use Saturio\DuckDB\Exception\ExecutePendingException;
use Saturio\DuckDB\Result\PendingResult;

class PendingResultTest extends TestCase
{
    public function testPendingResultException(): void
    {
        $db = DuckDB::create();

        $this->expectException(ExecutePendingException::class);
        $this->expectExceptionMessage('Error executing pending: Catalog Error: SET schema: No catalog + schema named "hello" found.');
        $preparedStatement = $db->preparedStatement('USE hello;');
        $pendingResult = $preparedStatement->pendingExecute();
        $pendingResult->execute();
    }

    public function testPendingResultWithProgress(): void
    {
        $db = DuckDB::create();
        $this->assertEquals(-1.0, $db->queryProgress()['percentage']);

        $db->query('CALL dbgen(sf = 1);');
        $db->query('SET threads=1;');
        $db->query('set enable_progress_bar=true;');
        $db->query('set enable_progress_bar_print=false;');

        $preparedStatement = $db->preparedStatement('PRAGMA tpch(9);');

        $pendingResult = $preparedStatement->pendingExecute();

        $this->assertEquals(0.0, $db->queryProgress()['percentage']);
        $this->assertEquals(0, $db->queryProgress()['rows_processed']);

        while (0.0 === $db->queryProgress()['percentage']) {
            $taskExecutionResult = $pendingResult->executeTask();
            $this->assertEquals(PendingResult::DUCKDB_PENDING_RESULT_NOT_READY, $taskExecutionResult);
        }

        $lastProgress = $db->queryProgress();
        $this->assertGreaterThan(0.0, $lastProgress['percentage']);

        do {
            $taskExecutionResult = $pendingResult->executeTask();
            $currentProgress = $db->queryProgress();
            $this->assertGreaterThanOrEqual($lastProgress['percentage'], $currentProgress['percentage']);
            if ($currentProgress['total_rows_to_process'] > 0) {
                $this->assertGreaterThanOrEqual($lastProgress['rows_processed'], $currentProgress['rows_processed']);
            }
            $lastProgress = $currentProgress;
        } while (!in_array($taskExecutionResult, [PendingResult::DUCKDB_PENDING_ERROR, PendingResult::DUCKDB_PENDING_RESULT_READY], true));

        if (PendingResult::DUCKDB_PENDING_ERROR === $taskExecutionResult) {
            throw new Exception('Error executing task');
        }

        $pendingResult->execute();
        $this->assertEquals(-1.0, $db->queryProgress()['percentage']);
    }
}
