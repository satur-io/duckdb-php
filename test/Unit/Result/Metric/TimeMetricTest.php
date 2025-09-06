<?php

declare(strict_types=1);

namespace Unit\Result\Metric;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\Result\Metric\TimeMetric;

class TimeMetricTest extends TestCase
{
    public function testEmptyMetrics(): void
    {
        $timeMetric = TimeMetric::create();
        self::assertEquals(0, $timeMetric->getTotalMilliseconds());
        self::assertEquals(0, $timeMetric->getPhpPercentage());
        self::assertEquals(0, $timeMetric->getPhpMilliseconds());
        self::assertEquals(100, $timeMetric->getNativePercentage());
        self::assertEquals(0, $timeMetric->getNativeMilliseconds());
    }

    public function testFiftyFiftyPercentage(): void
    {
        $sleepMicroseconds = 'Windows NT' === php_uname('s') ? 500000 : 10000;
        $timeMetric = TimeMetric::create();
        usleep($sleepMicroseconds);
        $timeMetric->switch();
        usleep($sleepMicroseconds);
        $timeMetric->end();

        self::assertEqualsWithDelta(50, $timeMetric->getPhpPercentage(), 1.0);
        self::assertEqualsWithDelta(50, $timeMetric->getNativePercentage(), 1.0);
    }

    public function testAllPHPPercentage(): void
    {
        $timeMetric = TimeMetric::create();
        usleep(1000);
        $timeMetric->end();

        self::assertEqualsWithDelta(100, $timeMetric->getPhpPercentage(), 1.0);
        self::assertEqualsWithDelta(0, $timeMetric->getNativePercentage(), 1.0);
    }

    public function testAllNativePercentage(): void
    {
        $timeMetric = TimeMetric::create();
        $timeMetric->switch();
        usleep(10000);
        $timeMetric->end();

        self::assertEqualsWithDelta(0, $timeMetric->getPhpPercentage(), 1.0);
        self::assertEqualsWithDelta(100, $timeMetric->getNativePercentage(), 1.0);
    }
}
