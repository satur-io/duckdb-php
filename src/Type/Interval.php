<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type;

class Interval
{
    public function __construct(
        private readonly int $months = 0,
        private readonly int $days = 0,
        private readonly int $microseconds = 0,
    ) {
    }

    public function getMonths(): int
    {
        return $this->months;
    }

    public function getDays(): int
    {
        return $this->days;
    }

    public function getMicroseconds(): int
    {
        return $this->microseconds;
    }

    public function __toString(): string
    {
        return sprintf("%s months %s days %s microseconds", $this->months, $this->days, $this->microseconds);
    }
}
