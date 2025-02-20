<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type;

use Saturio\DuckDB\Type\Math\MathLibInterface;

class UUID
{
    public function __construct(
        private readonly string $uuid,
    ) {
    }

    public static function fromHugeint(string $hugeint, MathLibInterface $math): self
    {
        $base = '170141183460469231731687303715884105727';
        $hyphensPositions = [8, 13, 18, 23];

        $hexString = str_pad(
            self::dechex(
                $math->sub(
                    $math->sub(
                        $hugeint,
                        $base
                    ),
                    '1'
                ), $math
            ),
            32,
            '0',
            STR_PAD_LEFT
        );

        return new self(
            array_reduce($hyphensPositions, function ($carry, $pos) { return self::addHyphen($carry, $pos); }, $hexString)
        );
    }

    private static function dechex(string $dec, MathLibInterface $math): string
    {
        $last = $math->mod($dec, '16');
        $remain = $math->div($math->sub($dec, $last), '16');

        if (0 == $remain) {
            return dechex((int) $last);
        } else {
            return self::dechex($remain, $math).dechex((int) $last);
        }
    }

    private static function addHyphen(string $string, int $pos): string
    {
        return substr_replace($string, '-', $pos, 0);
    }

    public function __toString(): string
    {
        return $this->uuid;
    }
}
