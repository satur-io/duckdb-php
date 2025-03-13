<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Converter;

use DateMalformedStringException;
use DateTime;
use FFI;
use Saturio\DuckDB\Exception\BigNumbersNotSupportedException;
use Saturio\DuckDB\Exception\InvalidTimeException;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
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
    private const PRECOMPUTED_2_POW_63 = '9223372036854775808';

    public function __construct(
        private readonly FFIDuckDB $ffi,
        private readonly ?MathLibInterface $math = null,
    ) {
    }

    public function getVarChar(NativeCData $data): string
    {
        $value = $data->value;
        $inlined = $value->inlined;
        $length = $inlined->length;
        if ($length <= 12) {
            $data = $inlined->inlined;

            return FFI::string($data);
        }
        $pointer = $value->pointer;
        $data = $pointer->ptr;

        return FFI::string($data, $length);
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
    public function getTimestampFromDuckDBTimestamp(NativeCData $timestamp, bool $timezoned = false): Timestamp
    {
        if (-9223372036854775807 === $timestamp->micros) {
            return new Timestamp(infinity: -1);
        }

        if (9223372036854775807 === $timestamp->micros) {
            return new Timestamp(infinity: 1);
        }

        $timestampStruct = $this->ffi->fromTimestamp($timestamp);

        return new Timestamp(
            $this->getDate($timestampStruct->date),
            $this->getTime($timestampStruct->time, isTimezoned: $timezoned),
        );
    }

    /**
     * @throws DateMalformedStringException
     * @throws InvalidTimeException
     */
    public function getTimestampFromDuckDBTimestampMs(NativeCData $timestamp): Timestamp
    {
        $datetime = new DateTime('1970-01-01 00:00:00');

        if (strlen((string) abs($timestamp->millis)) >= 14) {
            // \DateTime does not support a modify string with a number > 14 digits
            $this->modifyTimestampInBatches($timestamp, $datetime, $timestamp->millis, 'milliseconds');
        } else {
            $datetime->modify(sprintf('%+d milliseconds', $timestamp->millis));
        }

        return Timestamp::fromDateTime($datetime, TimePrecision::MILLISECONDS);
    }

    /**
     * @throws DateMalformedStringException
     * @throws InvalidTimeException
     */
    public function getTimestampFromDuckDBTimestampS(NativeCData $timestamp): Timestamp
    {
        $datetime = new DateTime('1970-01-01 00:00:00');
        $datetime->modify(sprintf('%+d seconds', $timestamp->seconds));

        return Timestamp::fromDateTime($datetime, TimePrecision::SECONDS);
    }

    /**
     * @throws InvalidTimeException|DateMalformedStringException
     */
    public function getTimestampFromDuckDBTimestampNs(NativeCData $timestamp): Timestamp
    {
        $datetime = new DateTime('1970-01-01 00:00:00');
        $nanoseconds = $timestamp->nanos;
        $milliseconds = intval($nanoseconds / 1000000);
        $nanosecondsReminder = $nanoseconds % 1000000000;

        if (strlen((string) abs($milliseconds)) >= 14) {
            // \DateTime does not support a modify string with a number > 14 digits
            $this->modifyTimestampInBatches($timestamp, $datetime, $milliseconds, 'milliseconds');
        } else {
            $datetime->modify(sprintf('%+d milliseconds', $milliseconds));
        }

        return Timestamp::fromDateTime($datetime, TimePrecision::NANOSECONDS, $nanosecondsReminder);
    }

    /**
     * @throws InvalidTimeException
     */
    public function getTimestampFromDuckDBTimestampTz(NativeCData $timestamp): Timestamp
    {
        return $this->getTimestampFromDuckDBTimestamp($timestamp, timezoned: true);
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

    public function getSignedBitInt(string|int $data): string
    {
        return $this->math->sub((string) $data, self::PRECOMPUTED_2_POW_64);
    }

    /**
     * @throws BigNumbersNotSupportedException
     */
    public function getHugeIntFromDuckDBHugeInt(NativeCData $data, bool $unsigned): int|string
    {
        $this->checkMath();
        $lower = $this->getBigIntFromDuckDBBigInt($data->lower, true);
        $upper = $this->getBigIntFromDuckDBBigInt($data->upper, $unsigned);

        return $this->math->add($this->math->mul((string) $upper, self::PRECOMPUTED_2_POW_64), (string) $lower);
    }

    public function getHugeIntFromUUID(NativeCData $data, bool $unsigned): int|string
    {
        $this->checkMath();
        $lower = $this->getBigIntFromDuckDBBigInt($data->lower, true);
        $upper = $this->math->add((string) $data->upper, self::PRECOMPUTED_2_POW_63);

        return $this->math->add($this->math->mul($upper, self::PRECOMPUTED_2_POW_64), (string) $lower);
    }

    /**
     * @throws BigNumbersNotSupportedException
     */
    public function getUUIDFromDuckDBHugeInt(NativeCData $data): UUID
    {
        $this->checkMath();

        $hugeintString = $this->getHugeIntFromUUID($data, true);

        return UUID::fromHugeint($hugeintString, $this->math);
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
            throw new BigNumbersNotSupportedException('You are querying a type that use integers > PHP_INT_MAX. Extension bcmath is not available.');
        }
    }

    /**
     * @throws DateMalformedStringException
     */
    private function modifyTimestampInBatches(NativeCData $timestamp, DateTime $datetime, int $number, string $unit): void
    {
        $positive = ($number >= 0);
        $sign = $positive ? '+' : '-';
        list($quotient, $mod) = $this->math->divmod((string) $number, sprintf('%s%d', $sign, 9999999999999));

        for ($i = 0; $i < $quotient; ++$i) {
            $datetime->modify(sprintf('%s%d %s', $sign, 9999999999999, $unit));
        }

        $datetime->modify(sprintf('%+d %s', $mod, $unit));
    }
}
