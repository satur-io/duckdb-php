<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type;

use JsonSerializable;

class Date implements JsonSerializable
{
    public function __construct(
        private readonly int $year, private readonly int $month, private readonly int $day)
    {
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function __toString(): string
    {
        return sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day);
    }

    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
}
