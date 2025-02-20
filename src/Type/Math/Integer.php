<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Math;

class Integer
{
    private function __construct(
        private readonly string $integerString,
    ) {
    }

    public static function fromString(string $integerString): self
    {
        return new self($integerString);
    }
}
