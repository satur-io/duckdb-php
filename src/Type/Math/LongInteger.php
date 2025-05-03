<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Math;

use JsonSerializable;
use Stringable;

class LongInteger implements Stringable, JsonSerializable
{
    private function __construct(
        private readonly string $integerString,
    ) {
    }

    public static function fromString(string $integerString): self
    {
        return new self($integerString);
    }

    public function __toString(): string
    {
        return $this->integerString;
    }

    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    public function toInt(MathLibInterface $math): int|false
    {
        return $math->toInt($this->integerString);
    }
}
