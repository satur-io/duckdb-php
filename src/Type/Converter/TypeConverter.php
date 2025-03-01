<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Converter;

use Saturio\DuckDB\Exception\BigNumbersNotSupportedException;
use Saturio\DuckDB\Exception\InvalidTimeException;
use Saturio\DuckDB\FFI\DuckDB;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Native\FFI;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;
use Saturio\DuckDB\Type\Date;
use Saturio\DuckDB\Type\Interval;
use Saturio\DuckDB\Type\Math\MathLib;
use Saturio\DuckDB\Type\Math\MathLibInterface;
use Saturio\DuckDB\Type\Time;
use Saturio\DuckDB\Type\TimePrecision;
use Saturio\DuckDB\Type\Timestamp;
use Saturio\DuckDB\Type\UUID;

class TypeConverter
{
    use GetDuckDBValue;

    private static MathLibInterface $math;
    private static NativeCData $decimal;

    public static function getVarChar(NativeCData $data, FFIDuckDB $ffi): string
    {
        $value = $data->value;
        if ($value->inlined->length <= 12) {
            $inlined = $value->inlined;
            $length = $inlined->length;
            $data = $inlined->inlined;

            return FFI::string($data, $length);
        }
        $pointer = $value->pointer;
        $length = $pointer->length;
        $data = $pointer->ptr;

        return FFI::string($data, $length);
    }

    public static function getStringFromBlob(NativeCData $data, FFIDuckDB $ffi): string
    {
        $string = self::getVarChar($data, $ffi);

        $blobString = '';
        for ($i = 0; $i < strlen($string); ++$i) {
            $blobString .= ctype_print($string[$i]) ? $string[$i] : '\x'.str_pad(strtoupper(dechex(ord($string[$i]))), 2, '0', STR_PAD_LEFT);
        }

        return $blobString;
    }

    public static function getDateFromDuckDBDate(
        NativeCData $date,
        FFIDuckDB $ffi,
    ): Date {
        $dateStruct = $ffi->fromDate($date);

        return self::getDate($dateStruct);
    }

    /**
     * @throws InvalidTimeException
     */
    public static function getTimeFromDuckDBTime(
        NativeCData $time,
        FFIDuckDB $ffi,
    ): Time {
        $timeStruct = $ffi->fromTime($time);

        return self::getTime($timeStruct);
    }

    /**
     * @throws InvalidTimeException
     */
    public static function getTimeFromDuckDBTimeTz(
        NativeCData $time,
        FFIDuckDB $ffi,
    ): Time {
        $timeStruct = $ffi->fromTimeTz($time);

        $time = self::getTime($timeStruct->time, true);

        return $time->setOffset($timeStruct->offset);
    }

    /**
     * @throws InvalidTimeException
     */
    public static function getTimestampFromDuckDBTimestamp(
        NativeCData $timestamp,
        FFIDuckDB $ffi,
    ): Timestamp {
        $timestampStruct = $ffi->fromTimestamp($timestamp);

        return new Timestamp(
            self::getDate($timestampStruct->date),
            self::getTime($timestampStruct->time),
        );
    }

    /**
     * @throws \DateMalformedStringException
     * @throws InvalidTimeException
     */
    public static function getTimestampFromDuckDBTimestampMs(
        NativeCData $timestamp,
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
        NativeCData $timestamp,
    ): Timestamp {
        $datetime = new \DateTime('1970-01-01 00:00:00');
        $datetime->modify("+ $timestamp->seconds seconds");

        return Timestamp::fromDateTime($datetime, TimePrecision::SECONDS);
    }

    /**
     * @throws InvalidTimeException|\DateMalformedStringException
     */
    public static function getTimestampFromDuckDBTimestampNs(
        NativeCData $timestamp,
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
        NativeCData $timestamp,
        DuckDB $ffi,
    ): Timestamp {
        $timestampStruct = $ffi->fromTimestamp($timestamp);

        return new Timestamp(
            self::getDate($timestampStruct->date),
            self::getTime($timestampStruct->time, isTimezoned: true),
        );
    }

    public static function getDate(NativeCData $dateStruct): Date
    {
        return new Date($dateStruct->year, $dateStruct->month, $dateStruct->day);
    }

    /**
     * @throws InvalidTimeException
     */
    public static function getTime(NativeCData $timeStruct, bool $isTimezoned = false): Time
    {
        return new Time(
            $timeStruct->hour,
            $timeStruct->min,
            $timeStruct->sec,
            microseconds: (int) trim((string) $timeStruct->micros, '0'),
            isTimeZoned: $isTimezoned,
        );
    }

    public static function getIntervalFromDuckDBInterval(NativeCData $data): Interval
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
    public static function getHugeIntFromDuckDBHugeInt(NativeCData $data): int|string
    {
        $lower = self::getUBigIntFromDuckDBUBigInt($data->lower);
        $upper = self::getUBigIntFromDuckDBUBigInt($data->upper);

        return self::getMath()->add(self::getMath()->mul((string) $upper, self::getMath()->pow('2', '64')), (string) $lower);
    }

    /**
     * @throws BigNumbersNotSupportedException
     */
    public static function getUUIDFromDuckDBHugeInt(NativeCData $data): UUID
    {
        $hugeint = self::getHugeIntFromDuckDBHugeInt($data);

        return UUID::fromHugeint($hugeint, self::getMath());
    }

    public static function getBitDuckDBBit(?NativeCData $data, FFIDuckDB $ffi): string
    {
        $value = $ffi->createBit($data);

        return $ffi->getVarchar($value);
    }

    public static function getBlobDuckDBlob(?NativeCData $data, FFIDuckDB $ffi): string
    {
        $value = $ffi->createBlob($data->data, $data->size);

        return $ffi->getVarchar($value);
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

    public static function getStringFromEnum(NativeCData $logicalType, int $entry, FFIDuckDB $ffi): string
    {
        return $ffi->enumDictionaryValue($logicalType, $entry);
    }
}
