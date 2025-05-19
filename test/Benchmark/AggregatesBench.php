<?php

declare(strict_types=1);

namespace Benchmark;

use PhpBench\Attributes as Bench;
use Saturio\DuckDB\DuckDB;

class AggregatesBench
{
    #[Bench\Warmup(2)]
    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchLibrary(): void
    {
        $duckDB = DuckDB::create();
        $result = $duckDB->query('SELECT * FROM (VALUES (10, 20),(30, 40),(50, 60));');
        foreach ($result->rows() as $row) {
            implode('|', $row).PHP_EOL;
        }
    }

    #[Bench\Warmup(2)]
    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchParquet(): void
    {
        $duckDB = DuckDB::create();
        $result = $duckDB->query(
            'SELECT "Reporting Year", avg("Gas Produced, MCF") as "AVG Gas Produced" 
                FROM ".phpbench/samples/oil-and-gas.parquet"
                WHERE "Reporting Year" BETWEEN 1985 AND 1990
                GROUP BY "Reporting Year";'
        );
        foreach ($result->rows() as $row) {
            implode('|', $row).PHP_EOL;
        }
    }

    #[Bench\Warmup(2)]
    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    public function benchMediumDatabase(): void
    {
        $duckDB = DuckDB::create('.phpbench/samples/dutch_railway_network.duckdb');
        $result = $duckDB->query(
            "SELECT
                        date_trunc('hour', station_service_time) AS window_start,
                        window_start + INTERVAL 1 HOUR AS window_end,
                        count(*) AS number_of_services
                    FROM main_main.ams_traffic_v
                    WHERE year(station_service_time) = 2024
                    GROUP BY ALL
                    ORDER BY 1;"
        );
        foreach ($result->rows() as $row) {
            implode('|', $row).PHP_EOL;
        }
    }
}
