<?php

namespace Benchmark;

use Generator;
use PhpBench\Attributes as Bench;
use Saturio\DuckDB\DuckDB;
use Saturio\DuckDB\Type\Type;

class QuickAppenderBench
{
    private const QUANTITY = 1000000;
    private const BATCH = 1000;
    private const VARCHAR_LENGTH = 100;

    // --- Provider de Métodos VARCHAR ---

    #[Bench\ParamProviders('provideVarcharAppenderMethods')]
    public function benchAppendVarchar(array $params): void
    {
        [
            'method' => $appendMethod,
            'expected_type' => $type,
            'value_arg2' => $arg2
        ] = $params;

        $this->runAppenderBench(
            'test_varchar',
            'varchar',
            $appendMethod,
            $type,
            $arg2
        );
    }

    public function provideVarcharAppenderMethods(): Generator
    {
        yield 'DefaultMethod' => ['method' => 'append', 'expected_type' => null, 'value_arg2' => null];
        yield 'DefaultMethodTyped' => ['method' => 'append', 'expected_type' => Type::DUCKDB_TYPE_VARCHAR, 'value_arg2' => null];
        yield 'VarcharMethod' => ['method' => 'appendVarchar', 'expected_type' => null, 'value_arg2' => null];
        yield 'VarcharLengthMethod' => ['method' => 'appendVarchar', 'expected_type' => null, 'value_arg2' => self::VARCHAR_LENGTH];
        yield 'QuickMethod' => ['method' => 'quickAppend', 'expected_type' => null, 'value_arg2' => null];
        yield 'QuickMethodInferType' => ['method' => 'quickAppend', 'expected_type' => null, 'value_arg2' => ['inferType' => true]];
        yield 'QuickMethodTyped' => ['method' => 'quickAppend', 'expected_type' => Type::DUCKDB_TYPE_VARCHAR, 'value_arg2' => null];
    }

    // --- Provider de Métodos INT64 ---

    #[Bench\ParamProviders('provideInt64AppenderMethods')]
    public function benchAppendInt64(array $params): void
    {
        [
            'method' => $appendMethod,
            'expected_type' => $type,
            'value_generator' => $generatorKey
        ] = $params;

        $arg2 = str_ends_with($generatorKey, '_infer') ? ['inferType' => true] : null;

        $this->runAppenderBench(
            'test_int64',
            $generatorKey,
            $appendMethod,
            $type,
            $arg2
        );
    }

    public function provideInt64AppenderMethods(): Generator
    {
        yield 'DefaultMethod' => ['method' => 'append', 'expected_type' => null, 'value_generator' => 'int64'];
        yield 'DefaultMethodTyped' => ['method' => 'append', 'expected_type' => Type::DUCKDB_TYPE_BIGINT, 'value_generator' => 'int64'];
        yield 'Int64Method' => ['method' => 'appendInt64', 'expected_type' => null, 'value_generator' => 'int64'];
        yield 'VarcharMethod' => ['method' => 'appendVarchar', 'expected_type' => null, 'value_generator' => 'int64_string'];
        yield 'QuickMethod' => ['method' => 'quickAppend', 'expected_type' => null, 'value_generator' => 'int64'];
        yield 'QuickMethodInferType' => ['method' => 'quickAppend', 'expected_type' => null, 'value_generator' => 'int64_infer'];
        yield 'QuickMethodTyped' => ['method' => 'quickAppend', 'expected_type' => Type::DUCKDB_TYPE_BIGINT, 'value_generator' => 'int64'];
    }

    // --- Provider de Métodos INT32 ---

    #[Bench\ParamProviders('provideInt32AppenderMethods')]
    public function benchAppendInt32(array $params): void
    {
        [
            'method' => $appendMethod,
            'expected_type' => $type,
            'value_generator' => $generatorKey
        ] = $params;

        $arg2 = str_ends_with($generatorKey, '_infer') ? ['inferType' => true] : null;

        $this->runAppenderBench(
            'test_int32',
            $generatorKey,
            $appendMethod,
            $type,
            $arg2
        );
    }

    public function provideInt32AppenderMethods(): Generator
    {
        yield 'Int64Method' => ['method' => 'appendInt64', 'expected_type' => null, 'value_generator' => 'int32'];
        yield 'Int32Method' => ['method' => 'appendInt32', 'expected_type' => null, 'value_generator' => 'int32'];
        yield 'VarcharMethod' => ['method' => 'appendVarchar', 'expected_type' => null, 'value_generator' => 'int32_string'];
        yield 'QuickMethod' => ['method' => 'quickAppend', 'expected_type' => null, 'value_generator' => 'int32'];
        yield 'QuickMethodInferType' => ['method' => 'quickAppend', 'expected_type' => null, 'value_generator' => 'int32_infer'];
        yield 'QuickMethodTyped' => ['method' => 'quickAppend', 'expected_type' => Type::DUCKDB_TYPE_INTEGER, 'value_generator' => 'int32'];
        yield 'QuickMethodV2' => ['method' => 'quickAppendV2', 'expected_type' => null, 'value_generator' => 'int32'];
        yield 'QuickMethodV2InferType' => ['method' => 'quickAppendV2', 'expected_type' => null, 'value_generator' => 'int32_infer'];
    }


    // --- Lógica Compartida (Generadores y Ejecutor) ---

    private function getVarcharValue(): string
    {
        return base64_encode(random_bytes(self::VARCHAR_LENGTH));
    }

    private function getInt64Value(): int
    {
        return rand(0, PHP_INT_MAX);
    }

    private function getInt32Value(): int
    {
        return rand(0, (int) (PHP_INT_MAX / 2));
    }

    private function getValueGenerator(string $key): callable
    {
        return match ($key) {
            'varchar' => fn() => $this->getVarcharValue(),
            'int64', 'int64_infer' => fn() => $this->getInt64Value(),
            'int64_string' => fn() => (string) $this->getInt64Value(),
            'int32', 'int32_infer' => fn() => $this->getInt32Value(),
            'int32_string' => fn() => (string) $this->getInt32Value(),
            default => throw new \InvalidArgumentException("Generador desconocido: $key"),
        };
    }

    private function runAppenderBench(
        string $tableName,
        string $generatorKey,
        string $appendMethod,
        int|string|null $type = null,
        int|array|null $arg2 = null
    ): void {
        $duckDB = DuckDB::create();
        $duckDB->query("CREATE TABLE IF NOT EXISTS $tableName (value VARCHAR);");

        $appender = $duckDB->appender($tableName);
        $valueGenerator = $this->getValueGenerator($generatorKey);

        $params = [];
        if ($type !== null) {
            $params[] = $type;
        }
        if (is_int($arg2) || is_string($arg2)) {
            $params[] = $arg2;
        }

        foreach (range(0, self::QUANTITY) as $i) {
            $value = $valueGenerator();

            if (is_array($arg2)) {
                $appender->$appendMethod($value, ...$params, ...$arg2);
            } else {
                $appender->$appendMethod($value, ...$params);
            }

            $appender->endRow();

            if ($i % self::BATCH === 0) {
                $appender->flush();
            }
        }

        $appender->flush();
    }
}