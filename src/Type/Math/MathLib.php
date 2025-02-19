<?php

declare(strict_types=1);

namespace SaturIo\DuckDB\Type\Math;

use SaturIo\DuckDB\Exception\BigNumbersNotSupportedException;

class MathLib implements MathLibInterface
{
    private MathLibInterface $math;

    /**
     * @throws BigNumbersNotSupportedException
     */
    public function __construct()
    {
        if (BCMathLib::available()) {
            $this->math = new BCMathLib();
        } else {
            throw new BigNumbersNotSupportedException('You are trying to read a number greater than PHP_INT_MAX, but bcmath extension is not available.');
        }
    }

    public function add(string $x, string $y): string
    {
        return $this->math->add($x, $y);
    }

    public function sub(string $x, string $y): string
    {
        return $this->math->sub($x, $y);
    }

    public function mul(string $x, string $y): string
    {
        return $this->math->mul($x, $y);
    }

    public function pow(string $x, string $y): string
    {
        return $this->math->pow($x, $y);
    }

    public function mod(string $x, string $y): string
    {
        return $this->math->mod($x, $y);
    }

    public function div(string $x, string $y): string
    {
        return $this->math->div($x, $y);
    }

    public static function available(): bool
    {
        return true;
    }
}
