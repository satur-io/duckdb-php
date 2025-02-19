<?php

declare(strict_types=1);

namespace Benchmark;

use PhpBench\Attributes as Bench;
use SaturIo\DuckDB\DuckDB;

class AggregatesBench
{
    #[Bench\Revs(1)]
    #[Bench\Iterations(3)]
    #[Bench\ParamProviders(['queries'])]
    public function benchLibrary(array $query): void
    {
        $duckDB = DuckDB::create();
        $result = $duckDB->query($query['query']);
        foreach ($result->rows() as $row) {
            implode('|', $row).PHP_EOL;
        }
    }

    public function queries(): \Generator
    {
        yield 'aggregation-from-long-dataset' => [
            'query' => implode(' ', [
                "CREATE TABLE measurements AS SELECT * FROM 'test/_data/measurements.csv';",
                'SELECT column0 as city, max(column1) as max, mean(column1) as mean, min(column1) as min FROM measurements GROUP BY column0 ORDER BY city;',
            ]),
        ];
        yield 'simple-int-query' => [
            'query' => 'SELECT * FROM (VALUES (10, 20),(30, 40),(50, 60));',
        ];
    }
}
