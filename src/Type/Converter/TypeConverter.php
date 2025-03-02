<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Converter;

use Saturio\DuckDB\Exception\BigNumbersNotSupportedException;
use Saturio\DuckDB\Exception\InvalidTimeException;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Native\FFI as NativeFFI;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;
use Saturio\DuckDB\Type\Date;
use Saturio\DuckDB\Type\Interval;
use Saturio\DuckDB\Type\Math\MathLibInterface;
use Saturio\DuckDB\Type\Time;
use Saturio\DuckDB\Type\TimePrecision;
use Saturio\DuckDB\Type\Timestamp;
use Saturio\DuckDB\Type\UUID;

class TypeConverter
{
    use GetDuckDBValue;
    private const PRECOMPUTED_2_POW_64 = '18446744073709551616';
    private static NativeCData $decimal;

    public function __construct(
        private readonly FFIDuckDB $ffi,
        private readonly ?MathLibInterface $math = null,
    ) {
    }

    public function getVarChar(NativeCData $data): string
    {
        $value = $data->value;
        if ($value->inlined->length <= 12) {
            $inlined = $value->inlined;
            $length = $inlined->length;
            $data = $inlined->inlined;

            return NativeFFI::string($data, $length);
        }
        $pointer = $value->pointer;
        $length = $pointer->length;
        $data = $pointer->ptr;

        return NativeFFI::string($data, $length);
    }

    public function getStringFromBlob(NativeCData $data): string
    {
        $string = $this->getVarChar($data);

        $blobString = '';
        for ($i = 0; $i < strlen($string); ++$i) {
            $blobString .= ctype_print($string[$i]) ? $string[$i] : '\x'.str_pad(strtoupper(dechex(ord($string[$i]))), 2, '0', STR_PAD_LEFT);
        }

        return $blobString;
    }

    public function getDateFromDuckDBDate(NativeCData $date): Date
    {
        $dateStruct = $this->ffi->fromDate($date);

        return $this->getDate($dateStruct);
    }

    /**
     * @throws InvalidTimeException
     */
    public function getTimeFromDuckDBTime(NativeCData $time): Time
    {
        $timeStruct = $this->ffi->fromTime($time);

        return $this->getTime($timeStruct);
    }

    /**
     * @throws InvalidTimeException
     */
    public function getTimeFromDuckDBTimeTz(NativeCData $time): Time
    {
        $timeStruct = $this->ffi->fromTimeTz($time);

        $time = $this->getTime($timeStruct->time, true);

        return $time->setOffset($timeStruct->offset);
    }

    /**
     * @throws InvalidTimeException
     */
    public function getTimestampFromDuckDBTimestamp(NativeCData $timestamp): Timestamp
    {
        $timestampStruct = $this->ffi->fromTimestamp($timestamp);

        return new Timestamp(
            $this->getDate($timestampStruct->date),
            $this->getTime($timestampStruct->time),
        );
    }

    /**
     * @throws \DateMalformedStringException
     * @throws InvalidTimeException
     */
    public function getTimestampFromDuckDBTimestampMs(NativeCData $timestamp): Timestamp
    {
        $datetime = new \DateTime('1970-01-01 00:00:00');
        $datetime->modify("+ $timestamp->millis milliseconds");

        return Timestamp::fromDateTime($datetime, TimePrecision::MILLISECONDS);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws InvalidTimeException
     */
    public function getTimestampFromDuckDBTimestampS(NativeCData $timestamp): Timestamp
    {
        $datetime = new \DateTime('1970-01-01 00:00:00');
        $datetime->modify("+ $timestamp->seconds seconds");

        return Timestamp::fromDateTime($datetime, TimePrecision::SECONDS);
    }

    /**
     * @throws InvalidTimeException|\DateMalformedStringException
     */
    public function getTimestampFromDuckDBTimestampNs(NativeCData $timestamp): Timestamp
    {
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
    public function getTimestampFromDuckDBTimestampTz(NativeCData $timestamp): Timestamp
    {
        $timestampStruct = $this->ffi->fromTimestamp($timestamp);

        return new Timestamp(
            $this->getDate($timestampStruct->date),
            $this->getTime($timestampStruct->time, isTimezoned: true),
        );
    }

    public function getDate(NativeCData $dateStruct): Date
    {
        return new Date($dateStruct->year, $dateStruct->month, $dateStruct->day);
    }

    /**
     * @throws InvalidTimeException
     */
    public function getTime(NativeCData $timeStruct, bool $isTimezoned = false): Time
    {
        return new Time(
            $timeStruct->hour,
            $timeStruct->min,
            $timeStruct->sec,
            microseconds: (int) trim((string) $timeStruct->micros, '0'),
            isTimeZoned: $isTimezoned,
        );
    }

    public function getIntervalFromDuckDBInterval(NativeCData $data): Interval
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
    public function getBigIntFromDuckDBBigInt(int $data, bool $unsigned): int|string
    {
        $this->checkMath();
        if (!$unsigned || $data >= 0) {
            return $data;
        }

        return $this->math->add((string) $data, self::PRECOMPUTED_2_POW_64);
    }

    /**
     * @throws BigNumbersNotSupportedException
     */
    public function getHugeIntFromDuckDBHugeInt(NativeCData $data, bool $unsigned): int|string
    {
        $this->checkMath();
        $lower = $this->getBigIntFromDuckDBBigInt($data->lower, true);
        $upper = $this->getBigIntFromDuckDBBigInt($data->upper, $unsigned);

        return $this->math->add($this->math->mul((string) $upper, $this->math->pow('2', '64')), (string) $lower);
    }

    /**
     * @throws BigNumbersNotSupportedException
     */
    public function getUUIDFromDuckDBHugeInt(NativeCData $data): UUID
    {
        $this->checkMath();
        $hugeint = $this->getHugeIntFromDuckDBHugeInt($data, unsigned: true);

        return UUID::fromHugeint($hugeint, $this->math);
    }

    public function getBitDuckDBBit(?NativeCData $data): string
    {
        $value = $this->ffi->createBit($data);

        return $this->ffi->getVarchar($value);
    }

    public function getBlobDuckDBlob(?NativeCData $data): string
    {
        $value = $this->ffi->createBlob($data->data, $data->size);

        return $this->ffi->getVarchar($value);
    }

    public function getStringFromEnum(NativeCData $logicalType, int $entry): string
    {
        return $this->ffi->enumDictionaryValue($logicalType, $entry);
    }

    /**
     * @throws BigNumbersNotSupportedException
     */
    private function checkMath(): void
    {
        if (!isset($this->math)) {
            throw new BigNumbersNotSupportedException('You are trying to read a number greater than PHP_INT_MAX or a  UUID, but bcmath extension is not available.');
        }
    }
}
