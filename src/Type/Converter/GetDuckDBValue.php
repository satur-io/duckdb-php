<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Converter;

use Saturio\DuckDB\Exception\UnsupportedTypeException;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;
use Saturio\DuckDB\Type\Date;
use Saturio\DuckDB\Type\Interval;
use Saturio\DuckDB\Type\Time;
use Saturio\DuckDB\Type\Timestamp;
use Saturio\DuckDB\Type\Type;
use Saturio\DuckDB\Type\TypeC;

trait GetDuckDBValue
{
    /**
     * @throws UnsupportedTypeException
     */
    public static function getDuckDBValue(
        string|bool|int|float|Date|Time|Timestamp|Interval $value, FFIDuckDB $ffi, ?Type $type = null,
    ): NativeCData {
        $type = $type ?? self::getInferredType($value);

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
            Type::DUCKDB_TYPE_DOUBLE => self::createFromScalar($value, $type, $ffi),
            Type::DUCKDB_TYPE_DATE => self::createFromDate($value, $ffi),
            Type::DUCKDB_TYPE_TIME => self::createFromTime($value, $ffi),
            Type::DUCKDB_TYPE_TIMESTAMP => self::createFromTimestamp($value, $ffi),
            Type::DUCKDB_TYPE_INTERVAL => self::createFromInterval($value, $ffi),
            default => throw new UnsupportedTypeException("Unsupported type: {$type->name}"),
        };
    }

    /**
     * @throws UnsupportedTypeException
     */
    private static function getInferredType(string|bool|int|float|Date|Time|Timestamp $value): Type
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

    private static function createFromScalar(
        string|bool|int|float $value, Type $type, FFIDuckDB $ffi,
    ): NativeCData {
        $ffiFunction = 'create'.ucfirst(TypeC::{$type->name}->value);

        return $ffi->{$ffiFunction}($value);
    }

    private static function createFromDate(
        Date $date, FFIDuckDB $ffi): NativeCData
    {
        $dateStruct = self::getDateStruct($ffi, $date);

        return $ffi->createDate($ffi->toDate($dateStruct));
    }

    private static function createFromTime(
        Time $time, FFIDuckDB $ffi): NativeCData
    {
        $timeStruct = self::getTimeStruct($ffi, $time);

        return $ffi->createTime($ffi->toTime($timeStruct));
    }

    private static function createFromTimestamp(
        Timestamp $timestamp, FFIDuckDB $ffi): NativeCData
    {
        $timestampStruct = $ffi->new('duckdb_timestamp_struct');

        $timestampStruct->date = self::getDateStruct($ffi, $timestamp->getDate());
        $timestampStruct->time = self::getTimeStruct($ffi, $timestamp->getTime());

        return $ffi->createTimestamp($ffi->toTimestamp($timestampStruct));
    }

    private static function createFromInterval(
        Interval $interval, FFIDuckDB $ffi): NativeCData
    {
        $intervalStruct = $ffi->new('duckdb_interval');

        $intervalStruct->months = $interval->getMonths();
        $intervalStruct->days = $interval->getDays();
        $intervalStruct->micros = $interval->getMicroseconds();

        return $ffi->createInterval($intervalStruct);
    }

    public static function getTimeStruct(FFIDuckDB $ffi, Time $time): ?NativeCData
    {
        $timeStruct = $ffi->new('duckdb_time_struct');

        $timeStruct->hour = $time->getHours();
        $timeStruct->min = $time->getMinutes();
        $timeStruct->sec = $time->getSeconds();
        $timeStruct->micros = (int) str_pad((string) $time->getMicroseconds(), 6, '0', STR_PAD_RIGHT);

        return $timeStruct;
    }

    public static function getDateStruct(FFIDuckDB $ffi, Date $date): ?NativeCData
    {
        $dateStruct = $ffi->new('duckdb_date_struct');

        $dateStruct->year = $date->getYear();
        $dateStruct->month = $date->getMonth();
        $dateStruct->day = $date->getDay();

        return $dateStruct;
    }
}
