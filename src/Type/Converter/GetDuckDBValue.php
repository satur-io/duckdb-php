<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Converter;

use Saturio\DuckDB\Exception\UnsupportedTypeException;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;
use Saturio\DuckDB\Type\Date;
use Saturio\DuckDB\Type\Interval;
use Saturio\DuckDB\Type\Math\Integer;
use Saturio\DuckDB\Type\Math\Integer as BigInteger;
use Saturio\DuckDB\Type\Time;
use Saturio\DuckDB\Type\Timestamp;
use Saturio\DuckDB\Type\Type;
use Saturio\DuckDB\Type\TypeC;

trait GetDuckDBValue
{
    /**
     * @throws UnsupportedTypeException
     */
    public function getDuckDBValue(
        string|bool|int|float|Date|Time|Timestamp|Interval|BigInteger $value, ?Type $type = null,
    ): NativeCData {
        $type = $type ?? $this->getInferredType($value);

        return match ($type) {
            Type::DUCKDB_TYPE_VARCHAR,
            Type::DUCKDB_TYPE_BOOLEAN,
            Type::DUCKDB_TYPE_TINYINT,
            Type::DUCKDB_TYPE_UTINYINT,
            Type::DUCKDB_TYPE_SMALLINT,
            Type::DUCKDB_TYPE_USMALLINT,
            Type::DUCKDB_TYPE_INTEGER,
            Type::DUCKDB_TYPE_UINTEGER,
            Type::DUCKDB_TYPE_BIGINT,
            Type::DUCKDB_TYPE_UBIGINT,
            Type::DUCKDB_TYPE_FLOAT,
            Type::DUCKDB_TYPE_DOUBLE => $this->createFromScalar($value, $type),
            Type::DUCKDB_TYPE_DATE => $this->createFromDate($value),
            Type::DUCKDB_TYPE_TIME => $this->createFromTime($value),
            Type::DUCKDB_TYPE_TIMESTAMP => $this->createFromTimestamp($value),
            Type::DUCKDB_TYPE_INTERVAL => $this->createFromInterval($value),
            Type::DUCKDB_TYPE_HUGEINT => $this->createFromHugeInt($value, false),
            Type::DUCKDB_TYPE_UHUGEINT => $this->createFromUhugeInt($value),
            default => throw new UnsupportedTypeException("Unsupported type: {$type->name}"),
        };
    }

    /**
     * @throws UnsupportedTypeException
     */
    private function getInferredType(string|bool|int|float|Date|Time|Timestamp $value): Type
    {
        if (is_bool($value)) {
            return Type::DUCKDB_TYPE_BOOLEAN;
        } elseif (is_int($value)) {
            return Type::DUCKDB_TYPE_INTEGER;
        } elseif (is_float($value)) {
            return Type::DUCKDB_TYPE_FLOAT;
        } elseif (is_string($value)) {
            return Type::DUCKDB_TYPE_VARCHAR;
        } elseif (is_a($value, Date::class)) {
            return Type::DUCKDB_TYPE_DATE;
        } elseif (is_a($value, Time::class)) {
            return Type::DUCKDB_TYPE_TIME;
        } elseif (is_a($value, Timestamp::class)) {
            return Type::DUCKDB_TYPE_TIMESTAMP;
        }

        $type = gettype($value);
        throw new UnsupportedTypeException("Couldn't get inferred type: {$type}");
    }

    private function createFromScalar(
        string|bool|int|float $value, Type $type,
    ): NativeCData {
        $ffiFunction = 'create'.ucfirst(TypeC::{$type->name}->value);

        return $this->ffi->{$ffiFunction}($value);
    }

    private function createFromDate(Date $date): NativeCData
    {
        $dateStruct = $this->getDateStruct($date);

        return $this->ffi->createDate($this->ffi->toDate($dateStruct));
    }

    private function createFromTime(Time $time): NativeCData
    {
        $timeStruct = $this->getTimeStruct($time);

        return $this->ffi->createTime($this->ffi->toTime($timeStruct));
    }

    private function createFromTimestamp(Timestamp $timestamp): NativeCData
    {
        $timestampStruct = $this->ffi->new('duckdb_timestamp_struct');

        $timestampStruct->date = $this->getDateStruct($timestamp->getDate());
        $timestampStruct->time = $this->getTimeStruct($timestamp->getTime());

        return $this->ffi->createTimestamp($this->ffi->toTimestamp($timestampStruct));
    }

    private function createFromInterval(Interval $interval): NativeCData
    {
        $intervalStruct = $this->ffi->new('duckdb_interval');

        $intervalStruct->months = $interval->getMonths();
        $intervalStruct->days = $interval->getDays();
        $intervalStruct->micros = $interval->getMicroseconds();

        return $this->ffi->createInterval($intervalStruct);
    }

    private function createFromHugeInt(string|int|BigInteger $integer): NativeCData
    {
        $hugeint = $this->ffi->new('duckdb_hugeint');

        $divmod = $this->math->divmod((string) $integer, $this->math->pow('2', '64'));

        $hugeint->lower = (string) $this->createUBigInt($divmod[1]);
        $hugeint->upper = $divmod[0];

        return $this->ffi->createHugeint($hugeint);
    }

    public function createFromUhugeInt(string|int|BigInteger $integer): NativeCData
    {
        $uhugeint = $this->ffi->new('duckdb_uhugeint');

        $divmod = $this->math->divmod((string) $integer, $this->math->pow('2', '64'));

        $uhugeint->lower = (string) $this->createUBigInt($divmod[1]);
        $uhugeint->upper = (string) $this->createUBigInt($divmod[0]);

        return $this->ffi->createUhugeint($uhugeint);
    }

    private function createUBigInt(string|int|BigInteger $integer): BigInteger
    {
        if ($this->math->comp((string) $integer, (string) PHP_INT_MAX) <= 0) { // Less than 2^63 - 1
            return Integer::fromString((string) $integer);
        }

        return Integer::fromString($this->math->sub((string) $integer, self::PRECOMPUTED_2_POW_64));
    }

    public function getTimeStruct(Time $time): ?NativeCData
    {
        $timeStruct = $this->ffi->new('duckdb_time_struct');

        $timeStruct->hour = $time->getHours();
        $timeStruct->min = $time->getMinutes();
        $timeStruct->sec = $time->getSeconds();
        $timeStruct->micros = (int) str_pad((string) $time->getMicroseconds(), 6, '0', STR_PAD_RIGHT);

        return $timeStruct;
    }

    public function getDateStruct(Date $date): ?NativeCData
    {
        $dateStruct = $this->ffi->new('duckdb_date_struct');

        $dateStruct->year = $date->getYear();
        $dateStruct->month = $date->getMonth();
        $dateStruct->day = $date->getDay();

        return $dateStruct;
    }
}
