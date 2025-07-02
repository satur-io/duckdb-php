<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result\Metric;

class TimeMetric
{
    private int $phpNanoseconds = 0;
    private int $nativeNanoseconds = 0;
    private float $queryLatency = 0;

    private int $startedTime;
    private bool $currentContextIsPhp = true;

    public static function create(): TimeMetric
    {
        $metric = new TimeMetric();
        $metric->start();

        return $metric;
    }

    public function getPhpMilliseconds(): float
    {
        return $this->phpNanoseconds / 1000000;
    }

    public function getNativeMilliseconds(): float
    {
        return $this->nativeNanoseconds / 1000000;
    }

    public function getTotalMilliseconds(): float
    {
        return ($this->phpNanoseconds + $this->nativeNanoseconds) / 1000000;
    }

    public function getPhpPercentage(): int
    {
        return intval((($this->phpNanoseconds / 1000000) / $this->getTotalMilliseconds()) * 100);
    }

    public function getNativePercentage(): int
    {
        return 100 - $this->getPhpPercentage();
    }

    public function getQueryLatency(): float
    {
        return $this->queryLatency;
    }

    public function setQueryLatency(float $queryLatency): self
    {
        $this->queryLatency = $queryLatency;

        return $this;
    }

    public function start(): void
    {
        $this->startedTime = hrtime(true);
    }

    public function switch(): void
    {
        $this->updateMetrics();

        $this->currentContextIsPhp = !$this->currentContextIsPhp;
        $this->startedTime = hrtime(true);
    }

    public function end(): void
    {
        $this->updateMetrics();
    }

    private function updateMetrics(): void
    {
        if (!isset($this->startedTime)) {
            return;
        }

        $elapsedTime = hrtime(true) - $this->startedTime;

        if ($this->currentContextIsPhp) {
            $this->phpNanoseconds += $elapsedTime;
        } else {
            $this->nativeNanoseconds += $elapsedTime;
        }
    }
}
