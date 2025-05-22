<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type;

use DateMalformedStringException;
use DateTime;
use DateTimeInterface;
use JsonSerializable;
use Saturio\DuckDB\Exception\InvalidTimeException;

class Timestamp implements JsonSerializable
{
    public function __construct(
        private readonly ?Date $date = null,
        private readonly ?Time $time = null,
        private readonly int $infinity = 0,
    ) {
    }

    public function getTime(): Time
    {
        return $this->time;
    }

    public function getDate(): Date
    {
        return $this->date;
    }

    public function __toString(): string
    {
        return 0 === $this->infinity ? $this->date.' '.$this->time : $this->infinityToString();
    }

    private function infinityToString(): string
    {
        return $this->infinity < 0 ? '-infinity' : '+infinity';
    }

    /**
     * @throws InvalidTimeException
     */
    public static function fromDatetime(
        DateTimeInterface $dateTime,
        TimePrecision $precision = TimePrecision::MICROSECONDS,
        ?int $nanoseconds = null,
    ): self {
        if (null !== $nanoseconds and TimePrecision::NANOSECONDS !== $precision) {
            throw new InvalidTimeException('Nanoseconds param is only supported in NANOSECONDS precision');
        }

        $time = match ($precision) {
            TimePrecision::SECONDS => new Time(
                (int) $dateTime->format('H'),
                (int) $dateTime->format('i'),
                (int) $dateTime->format('s'),
            ),
            TimePrecision::MILLISECONDS => new Time(
                (int) $dateTime->format('H'),
                (int) $dateTime->format('i'),
                (int) $dateTime->format('s'),
                milliseconds: (int) $dateTime->format('v'),
            ),
            TimePrecision::MICROSECONDS => new Time(
                (int) $dateTime->format('H'),
                (int) $dateTime->format('i'),
                (int) $dateTime->format('s'),
                microseconds: (int) $dateTime->format('u'),
            ),
            TimePrecision::NANOSECONDS => new Time(
                (int) $dateTime->format('H'),
                (int) $dateTime->format('i'),
                (int) $dateTime->format('s'),
                nanoseconds: $nanoseconds,
            ),
        };

        return new self(
            new Date(
                (int) $dateTime->format('Y'),
                (int) $dateTime->format('m'),
                (int) $dateTime->format('d'),
            ),
            $time,
        );
    }

    /**
     * @throws DateMalformedStringException
     */
    public function toDateTime(): DateTime
    {
        return new DateTime($this->__toString());
    }

    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
}
