<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type;

use Saturio\DuckDB\Exception\InvalidTimeException;

class Time
{
    private int $nanoseconds;

    /**
     * @throws InvalidTimeException
     */
    public function __construct(
        private readonly int $hours = 0,
        private readonly int $minutes = 0,
        private readonly int $seconds = 0,
        ?int $milliseconds = null,
        ?int $microseconds = null,
        ?int $nanoseconds = null,
        private readonly bool $isTimeZoned = false,
        private int $offset = 0,
    ) {
        if (count(array_filter([$milliseconds, $microseconds, $nanoseconds], function ($item) { return !is_null($item); })) > 1) {
            throw new InvalidTimeException('Only one second fraction time is allowed.');
        }

        $this->nanoseconds = $nanoseconds ?? ($microseconds ? $microseconds * 1000 : ($milliseconds ? $milliseconds * 1000000 : 0));
    }

    public function getHours(): int
    {
        return $this->hours;
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function getMicroseconds(): int
    {
        return $this->nanoseconds / 1000;
    }

    public function __toString(): string
    {
        return sprintf('%02d:%02d:%02d.%d', $this->hours, $this->minutes, $this->seconds, $this->nanoseconds);
    }
}
