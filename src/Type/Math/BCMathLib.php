<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Math;

class BCMathLib extends AbstractMathLib
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

    public function divmod(string $x, string $y): array
    {
        if (PHP_VERSION_ID < 804000) {
            return [
                bcdiv($x, $y),
                bcmod($x, $y),
            ];
        }

        return bcdivmod($x, $y);
    }

    public function comp(string $x, string $y): int
    {
        return bccomp($x, $y);
    }
}
