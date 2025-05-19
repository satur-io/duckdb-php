<?php

declare(strict_types=1);

namespace Benchmark;

use Generator;
use PhpBench\Attributes as Bench;
use Saturio\DuckDB\DuckDB;

class DataTypeConversionBench
{
    #[Bench\Warmup(2)]
    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    public function benchAllTypes(): void
    {
        $duckDB = DuckDB::create();
        $result = $duckDB->query('SELECT * FROM test_all_types();');
        $rows = $result->rows();
        iterator_apply($rows, function ($rows) {
            implode('|', array_map(fn ($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v, $rows->current()));

            return true;
        }, [$rows]);
    }

    #[Bench\Warmup(2)]
    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    #[Bench\ParamProviders('varcharQueries')]
    public function benchVarchar(array $query): void
    {
        $duckDB = DuckDB::create();
        $result = $duckDB->query($query['query']);
        $rows = $result->rows();
        iterator_apply($rows, function ($rows) {
            implode('|', array_map(fn ($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v, $rows->current()));

            return true;
        }, [$rows]);
    }

    public function varcharQueries(): Generator
    {
        yield '100 000 rows one character' => ['query' => "SELECT * FROM repeat('h', 100000);"];
        yield '100 000 rows 12 character' => ['query' => "SELECT * FROM repeat('123456789012', 100000);"];
        yield '100 000 rows 13 character' => ['query' => "SELECT * FROM repeat('1234567890123', 100000);"];
        yield '100 000 rows 40 character' => ['query' => "SELECT * FROM repeat('1234567890123456789012345678901234567890', 100000);"];
    }
}
