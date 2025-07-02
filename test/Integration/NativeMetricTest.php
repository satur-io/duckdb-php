<?php

declare(strict_types=1);

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

        $this->assertEquals(
            $latency,
            $result->metric->getQueryLatency()
        );

        $this->assertEqualsWithDelta(
            $result->metric->getPhpMilliseconds() + $result->metric->getNativeMilliseconds(),
            $result->metric->getTotalMilliseconds(),
            delta: 5
        );
    }

    public function testMetricHighPhpLoad()
    {
        $duckDB = DuckDB::create();

        $duckDB->query("PRAGMA enable_profiling = 'no_output';");
        $result = $duckDB->query("SELECT * FROM repeat('123456789012', 100000);");

        // Before read result, even the query is materialized no significant PHP latency
        $this->assertGreaterThanOrEqual(
            99,
            $result->metric->getNativePercentage()
        );
        $this->assertLessThanOrEqual(
            1,
            $result->metric->getPhpPercentage()
        );

        // After loop over the results and convert from C types to PHP types, high PHP percentage expected
        iterator_to_array($result->rows());
        $this->assertGreaterThanOrEqual(
            99,
            $result->metric->getPhpPercentage()
        );
        $this->assertLessThanOrEqual(
            1,
            $result->metric->getNativePercentage()
        );
    }
}
