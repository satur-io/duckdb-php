<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Math;

use Stringable;

class Integer implements Stringable
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
}
