<?php

namespace Integration;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\DuckDB;

class NativeMetricTest extends TestCase
{
    public function testNativeMetric()
    {
        $duckDB = DuckDB::create();

        $duckDB->query("PRAGMA enable_profiling = 'no_output';");
        $result = $duckDB->query('SUMMARIZE TABLE "https://blobs.duckdb.org/data/Star_Trek-Season_1.csv";');

        $latency = $duckDB->getLatency();

        $this->assertIsFloat($latency);
        $this->assertEqualsWithDelta(
            $result->metric->getNativeMilliseconds(),
            $latency * 1000,
            5
        );
    }
}
