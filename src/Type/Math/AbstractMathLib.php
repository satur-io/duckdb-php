<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Math;

abstract class AbstractMathLib implements MathLibInterface
{
    abstract public function add(string $x, string $y): string;

    abstract public function sub(string $x, string $y): string;

    abstract public function mul(string $x, string $y): string;

    abstract public function pow(string $x, string $y): string;

    abstract public function mod(string $x, string $y): string;

    abstract public function div(string $x, string $y): string;

    abstract public function divmod(string $x, string $y): array;

    abstract public function comp(string $x, string $y): int;

    abstract public static function available(): bool;

    public function toInt(string $x): int|false
    {
        return 1 === $this->comp($x, (string) PHP_INT_MAX) ? false : (int) $x;
    }
}
