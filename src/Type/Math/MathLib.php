<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Math;

use Saturio\DuckDB\Exception\BigNumbersNotSupportedException;

class MathLib implements MathLibInterface
{
    private MathLibInterface $math;

    /**
     * @throws BigNumbersNotSupportedException
     */
    private function __construct()
    {
        if (BCMathLib::available()) {
            $this->math = new BCMathLib();
        }
    }

    public static function create(): ?self
    {
        if (BCMathLib::available()) {
            $math = new self();
            $math->math = new BCMathLib();

            return $math;
        }

        return null;
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

    public function divmod(string $x, string $y): array
    {
        return $this->math->divmod($x, $y);
    }

    public function comp(string $x, string $y): int
    {
        return $this->math->comp($x, $y);
    }

    public function toInt(string $x): int|false
    {
        return $this->math->toInt($x);
    }

    public static function available(): bool
    {
        return true;
    }
}
