<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Math;

interface MathLibInterface
{
    public function add(string $x, string $y): string;

    public function sub(string $x, string $y): string;

    public function mul(string $x, string $y): string;

    public function pow(string $x, string $y): string;

    public function mod(string $x, string $y): string;

    public function div(string $x, string $y): string;

    /**
     * @return array<int, string>
     */
    public function divmod(string $x, string $y): array;

    public function comp(string $x, string $y): int;

    public function toInt(string $x): int|false;

    public static function available(): bool;
}
