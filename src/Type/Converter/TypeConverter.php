<?php

declare(strict_types=1);

namespace SaturIo\DuckDB\Type\Converter;

use SaturIo\DuckDB\Exception\BigNumbersNotSupportedException;
use SaturIo\DuckDB\Exception\InvalidTimeException;
use SaturIo\DuckDB\FFI\CData;
use SaturIo\DuckDB\FFI\CDataInterface;
use SaturIo\DuckDB\FFI\DuckDB;
use SaturIo\DuckDB\FFI\DuckDB as FFIDuckDB;
use SaturIo\DuckDB\Type\Date;
use SaturIo\DuckDB\Type\Interval;
use SaturIo\DuckDB\Type\Math\MathLib;
use SaturIo\DuckDB\Type\Math\MathLibInterface;
use SaturIo\DuckDB\Type\Time;
use SaturIo\DuckDB\Type\TimePrecision;
use SaturIo\DuckDB\Type\Timestamp;
use SaturIo\DuckDB\Type\UUID;

class TypeConverter
{
    use GetDuckDBValue;

    private static MathLibInterface $math;
    private static CDataInterface $decimal;

    public static function getVarChar(CDataInterface $data, FFIDuckDB $ffi): string
    {
        $value = &$data->cdata->value;
        if ($value->inlined->length <= 12) {
            $inlined = $value->inlined;
            $length = $inlined->length;
            $data->cdata = $inlined->inlined;

            return $ffi->string($data, $length);
        }
        $pointer = $value->pointer;
        $length = $pointer->length;
        $data->cdata = $pointer->ptr;

        return $ffi->string($data, $length);
    }

    public static function getDateFromDuckDBDate(
        CDataInterface $date,
        FFIDuckDB $ffi,
    ): Date {
        $dateStruct = $ffi->fromDate($date);

        return self::getDate($dateStruct);
    }

    /**
     * @throws InvalidTimeException
     */
    public static function getTimeFromDuckDBTime(
        CDataInterface $time,
        FFIDuckDB $ffi,
    ): Time {
        $timeStruct = $ffi->fromTime($time);

        return self::getTime($timeStruct);
    }

    /**
     * @throws InvalidTimeException
     */
    public static function getTimestampFromDuckDBTimestamp(
        CDataInterface $timestamp,
        FFIDuckDB $ffi,
    ): Timestamp {
        $timestampStruct = $ffi->fromTimestamp($timestamp);

        return new Timestamp(
            self::getDate(new CData($timestampStruct->date)),
            self::getTime(new CData($timestampStruct->time)),
        );
    }

    /**
     * @throws \DateMalformedStringException
     * @throws InvalidTimeException
     */
    public static function getTimestampFromDuckDBTimestampMs(
        CDataInterface $timestamp,
    ): Timestamp {
        $datetime = new \DateTime('1970-01-01 00:00:00');
        $datetime->modify("+ $timestamp->millis milliseconds");

        return Timestamp::fromDateTime($datetime, TimePrecision::MILLISECONDS);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws InvalidTimeException
     */
    public static function getTimestampFromDuckDBTimestampS(
        CDataInterface $timestamp,
    ): Timestamp {
        $datetime = new \DateTime('1970-01-01 00:00:00');
        $datetime->modify("+ $timestamp->seconds seconds");

        return Timestamp::fromDateTime($datetime, TimePrecision::SECONDS);
    }

    /**
     * @throws InvalidTimeException|\DateMalformedStringException
     */
    public static function getTimestampFromDuckDBTimestampNs(
        CDataInterface $timestamp,
    ): Timestamp {
        $datetime = new \DateTime('1970-01-01 00:00:00');
        $nanoseconds = $timestamp->nanos;
        $milliseconds = intval($nanoseconds / 1000000);
        $nanosecondsReminder = $nanoseconds % 1000000000;

        $datetime->modify("+ $milliseconds milliseconds");

        return Timestamp::fromDateTime($datetime, TimePrecision::NANOSECONDS, $nanosecondsReminder);
    }

    /**
     * @throws InvalidTimeException
     */
    public static function getTimestampFromDuckDBTimestampTz(
        CDataInterface $timestamp,
        DuckDB $ffi,
    ): Timestamp {
        $timestampStruct = $ffi->fromTimestamp($timestamp);

        return new Timestamp(
            self::getDate(new CData($timestampStruct->date)),
            self::getTime(new CData($timestampStruct->time), isTimezoned: true),
        );
    }

    public static function getDate(CDataInterface $dateStruct): Date
    {
        return new Date($dateStruct->year, $dateStruct->month, $dateStruct->day);
    }

    /**
     * @throws InvalidTimeException
     */
    public static function getTime(CDataInterface $timeStruct, bool $isTimezoned = false): Time
    {
        return new Time(
            $timeStruct->hour,
            $timeStruct->min,
            $timeStruct->sec,
            microseconds: (int) trim((string) $timeStruct->micros, '0'),
            isTimeZoned: $isTimezoned,
        );
    }

    public static function getIntervalFromDuckDBInterval(CDataInterface $data): Interval
    {
        return new Interval(
            months: $data->months,
            days: $data->days,
            microseconds: $data->micros,
        );
    }

    /**
     * @throws BigNumbersNotSupportedException
     */
    public static function getUBigIntFromDuckDBUBigInt(int $data): int|string
    {
        if ($data >= 0) {
            return $data;
        }

        return self::getMath()->add((string) PHP_INT_MAX, self::getMath()->add((string) PHP_INT_MAX, (string) ($data + 2)));
    }

    /**
     * @throws BigNumbersNotSupportedException
     */
    public static function getHugeIntFromDuckDBHugeInt(CDataInterface $data): int|string
    {
        $lower = self::getUBigIntFromDuckDBUBigInt($data->lower);
        $upper = self::getUBigIntFromDuckDBUBigInt($data->upper);

        return self::getMath()->add(self::getMath()->mul((string) $upper, self::getMath()->pow('2', '64')), (string) $lower);
    }

    /**
     * @throws BigNumbersNotSupportedException
     */
    public static function getUUIDFromDuckDBHugeInt(CDataInterface $data): UUID
    {
        $hugeint = self::getHugeIntFromDuckDBHugeInt($data);

        return UUID::fromHugeint($hugeint, self::getMath());
    }

    /**
     * @throws BigNumbersNotSupportedException
     */
    public static function getMath(): MathLibInterface
    {
        if (empty(self::$math)) {
            self::$math = new MathLib();
        }

        return self::$math;
    }
}
