<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Math;

class BCMathLib implements MathLibInterface
{
    public static function available(): bool
    {
        return extension_loaded('bcmath');
    }

    public function add(string $x, string $y): string
    {
        return bcadd($x, $y);
    }

    public function sub(string $x, string $y): string
    {
        return bcsub($x, $y);
    }

    public function mul(string $x, string $y): string
    {
        return bcmul($x, $y);
    }

    public function pow(string $x, string $y): string
    {
        return bcpow($x, $y);
    }

    public function mod(string $x, string $y): string
    {
        return bcmod($x, $y);
    }

    public function div(string $x, string $y): string
    {
        return bcdiv($x, $y);
    }
}
