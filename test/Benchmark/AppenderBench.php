<?php

namespace Benchmark;

use PhpBench\Attributes as Bench;
use Saturio\DuckDB\DuckDB;
use Saturio\DuckDB\Type\Type;

class AppenderBench
{
    private const QUANTITY = 1000000;
    private const BATCH = 1000;
    private const VARCHAR_LENGTH = 100;

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharDefaultMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->append(base64_encode(random_bytes(self::VARCHAR_LENGTH)));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharDefaultMethodTyped(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->append(base64_encode(random_bytes(self::VARCHAR_LENGTH)), Type::DUCKDB_TYPE_VARCHAR);
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharVarcharMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendVarchar(base64_encode(random_bytes(self::VARCHAR_LENGTH)));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharVarcharLengthMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendVarchar(base64_encode(random_bytes(self::VARCHAR_LENGTH)), self::VARCHAR_LENGTH);
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharDefaultMethodV2(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendV2(base64_encode(random_bytes(self::VARCHAR_LENGTH)));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharDefaultMethodV2Typed(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendV2(base64_encode(random_bytes(self::VARCHAR_LENGTH)), Type::DUCKDB_TYPE_VARCHAR);
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharQuickMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->quickAppend(base64_encode(random_bytes(self::VARCHAR_LENGTH)));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharQuickMethodTyped(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->quickAppend(base64_encode(random_bytes(self::VARCHAR_LENGTH)), Type::DUCKDB_TYPE_VARCHAR);
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt64DefaultMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->append(rand(0, PHP_INT_MAX));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt64DefaultMethodTyped(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->append(rand(0, PHP_INT_MAX), Type::DUCKDB_TYPE_BIGINT);
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt64Int64Method(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendInt64(rand(0, PHP_INT_MAX));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt64VarcharMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendVarchar((string) rand(0, PHP_INT_MAX));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt64DefaultMethodV2(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendV2(rand(0, PHP_INT_MAX));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt64DefaultMethodV2Typed(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendV2(rand(0, PHP_INT_MAX), Type::DUCKDB_TYPE_BIGINT);
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt66QuickMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->quickAppend(rand(0, PHP_INT_MAX));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt66QuickMethodTyped(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->quickAppend(rand(0, PHP_INT_MAX), Type::DUCKDB_TYPE_BIGINT);
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int32'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt32Int64Method(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int32 (value INT8);');

        $appender = $duckDB->appender('test_int32');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendInt64(rand(0,  (int) (PHP_INT_MAX/2)));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int32'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt32Int32Method(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int32 (value INT8);');

        $appender = $duckDB->appender('test_int32');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendInt32(rand(0,  (int) (PHP_INT_MAX/2)));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int32'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt32VarcharMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int32 (value INT8);');

        $appender = $duckDB->appender('test_int32');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendVarchar((string) rand(0, (int) (PHP_INT_MAX/2)));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int32'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt32QuickMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int32 (value INT8);');

        $appender = $duckDB->appender('test_int32');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->quickAppend(rand(0, (int) (PHP_INT_MAX/2)));
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int32'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt32QuickMethodTyped(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int32 (value INT8);');

        $appender = $duckDB->appender('test_int32');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->quickAppend(rand(0, (int) (PHP_INT_MAX/2)), Type::DUCKDB_TYPE_INTEGER);
            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }
}
