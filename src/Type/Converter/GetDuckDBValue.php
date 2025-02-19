<?php

declare(strict_types=1);

namespace SaturIo\DuckDB\Type\Converter;

use SaturIo\DuckDB\Exception\UnsupportedTypeException;
use SaturIo\DuckDB\FFI\CDataInterface;
use SaturIo\DuckDB\FFI\DuckDB as FFIDuckDB;
use SaturIo\DuckDB\Type\Date;
use SaturIo\DuckDB\Type\Time;
use SaturIo\DuckDB\Type\Timestamp;
use SaturIo\DuckDB\Type\Type;
use SaturIo\DuckDB\Type\TypeC;

trait GetDuckDBValue
{
    /**
     * @throws UnsupportedTypeException
     */
    public static function getDuckDBValue(
        string|bool|int|float|Date|Time|Timestamp $value, FFIDuckDB $ffi, ?Type $type = null,
    ): CDataInterface {
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
    ): CDataInterface {
        $ffiFunction = 'create'.ucfirst(TypeC::{$type->name}->value);

        return $ffi->{$ffiFunction}($value);
    }

    private static function createFromDate(
        Date $date, FFIDuckDB $ffi): CDataInterface
    {
        $dateStruct = self::getDateStruct($ffi, $date);

        return $ffi->createDate($ffi->toDate($dateStruct));
    }

    private static function createFromTime(
        Time $time, FFIDuckDB $ffi): CDataInterface
    {
        $timeStruct = self::getTimeStruct($ffi, $time);

        return $ffi->createTime($ffi->toTime($timeStruct));
    }

    private static function createFromTimestamp(
        Timestamp $timestamp, FFIDuckDB $ffi): CDataInterface
    {
        $timestampStruct = $ffi->new('duckdb_timestamp_struct');

        $timestampStruct->date = self::getDateStruct($ffi, $timestamp->getDate())->cdata;
        $timestampStruct->time = self::getTimeStruct($ffi, $timestamp->getTime())->cdata;

        return $ffi->createTimestamp($ffi->toTimestamp($timestampStruct));
    }

    public static function getTimeStruct(FFIDuckDB $ffi, Time $time): ?CDataInterface
    {
        $timeStruct = $ffi->new('duckdb_time_struct');

        $timeStruct->hour = $time->getHours();
        $timeStruct->min = $time->getMinutes();
        $timeStruct->sec = $time->getSeconds();
        $timeStruct->micros = (int) str_pad((string) $time->getMicroseconds(), 6, '0', STR_PAD_RIGHT);

        return $timeStruct;
    }

    public static function getDateStruct(FFIDuckDB $ffi, Date $date): ?CDataInterface
    {
        $dateStruct = $ffi->new('duckdb_date_struct');

        $dateStruct->year = $date->getYear();
        $dateStruct->month = $date->getMonth();
        $dateStruct->day = $date->getDay();

        return $dateStruct;
    }
}
