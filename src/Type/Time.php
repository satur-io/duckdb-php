<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type;

use Saturio\DuckDB\Exception\InvalidTimeException;

class Time
{
    /**
     * @throws InvalidTimeException
     */
    public function __construct(
        private readonly int $hours = 0,
        private readonly int $minutes = 0,
        private readonly int $seconds = 0,
        private readonly ?int $milliseconds = null,
        private readonly ?int $microseconds = null,
        private readonly ?int $nanoseconds = null,
        private readonly bool $isTimeZoned = false,
        private int $offset = 0,
    ) {
        if (count(array_filter([$this->milliseconds, $this->microseconds, $this->nanoseconds], function ($item) { return !is_null($item); })) > 1) {
            throw new InvalidTimeException('Only one second fraction time is allowed.');
        }
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

    public function getMilliseconds(): int
    {
        return $this->milliseconds ?? (int) ($this->getMicroseconds() / 1000) ?? (int) ($this->getNanoseconds() / 1000000);
    }

    public function getMicroseconds(): int
    {
        return $this->microseconds ?? (int) ($this->getMilliseconds() * 1000) ?? (int) ($this->getNanoseconds() / 1000);
    }

    public function getNanoseconds(): int
    {
        return $this->nanoseconds ?? (int) ($this->getMicroseconds() * 1000) ?? (int) ($this->getMilliseconds() / 1000000);
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%02d:%02d:%02d.%d', $this->hours, $this->minutes, $this->seconds, $this->microseconds);
    }
}
