<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type;

use JsonSerializable;
use Saturio\DuckDB\Type\Math\LongInteger as BigInteger;
use Saturio\DuckDB\Type\Math\MathLibInterface;

class UUID implements JsonSerializable
{
    public function __construct(
        private readonly string $uuid,
    ) {
    }

    public static function fromHugeint(string $hugeint, MathLibInterface $math): self
    {
        $hyphensPositions = [8, 13, 18, 23];

        $hexString = str_pad(
            self::dechex($hugeint, $math
            ),
            32,
            '0',
            STR_PAD_LEFT
        );

        return new self(
            array_reduce($hyphensPositions, fn ($carry, $pos) => self::addHyphen($carry, $pos), $hexString)
        );
    }

    public function toInt(MathLibInterface $math): BigInteger
    {
        $hexString = str_replace('-', '', $this->uuid);
        $hexdec = self::hexdec($hexString, $math);

        return BigInteger::fromString($hexdec);
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

    private static function hexdec(string $hex, MathLibInterface $math): string
    {
        $dec = '0';
        $length = strlen($hex);

        for ($i = 0; $i < $length; ++$i) {
            $char = $hex[$i];
            $value = hexdec($char);
            $dec = $math->add($math->mul($dec, '16'), (string) $value);
        }

        return $dec;
    }

    private static function addHyphen(string $string, int $pos): string
    {
        return substr_replace($string, '-', $pos, 0);
    }

    public function __toString(): string
    {
        return $this->uuid;
    }

    public function jsonSerialize(): string
    {
        return $this->__toString();
    }
}
