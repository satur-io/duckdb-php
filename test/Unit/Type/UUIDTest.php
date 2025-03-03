<?php

declare(strict_types=1);

namespace Unit\Type;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\Type\Math\MathLib;
use Saturio\DuckDB\Type\Math\MathLibInterface;
use Saturio\DuckDB\Type\UUID;

class UUIDTest extends TestCase
{
    private MathLibInterface $math;

    public function setUp(): void
    {
        $this->math = MathLib::create();
    }

    public function testFromHugeIntBig(): void
    {
        $uuid = UUID::fromHugeint('224183855108044314217608774173861578155', $this->math);
        $this->assertEquals('a8a83d6e-0603-4c4f-bdb7-fde9fd7785ab', $uuid->__toString());
    }

    public function testFromHugeInt(): void
    {
        $uuid = UUID::fromHugeint('219007504285336230225095696181956220754', $this->math);
        $this->assertEquals('a4c34fa1-4aa6-41a4-96c1-5fad5e809752', $uuid->__toString());
    }

    public function testToInt(): void
    {
        $uuid = UUID::fromHugeint('219007504285336230225095696181956220754', $this->math);
        $this->assertEquals('219007504285336230225095696181956220754', $uuid->toInt($this->math)->__toString());
    }
}
