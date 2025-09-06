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
        $sleepMicroseconds = 'Windows NT' === php_uname('s') ? 1000000 : 1000;
        $timeMetric = TimeMetric::create();
        usleep($sleepMicroseconds);
        $timeMetric->switch();
        usleep($sleepMicroseconds);
        $timeMetric->end();

        self::assertEquals(50, $timeMetric->getPhpPercentage());
        self::assertEquals(50, $timeMetric->getNativePercentage());
    }

    public function testAllPHPPercentage(): void
    {
        $timeMetric = TimeMetric::create();
        usleep(1000);
        $timeMetric->end();

        self::assertEquals(100, $timeMetric->getPhpPercentage());
        self::assertEquals(0, $timeMetric->getNativePercentage());
    }

    public function testAllNativePercentage(): void
    {
        $timeMetric = TimeMetric::create();
        $timeMetric->switch();
        usleep(1000);
        $timeMetric->end();

        self::assertEquals(0, $timeMetric->getPhpPercentage());
        self::assertEquals(100, $timeMetric->getNativePercentage());
    }
}
