<?php

namespace Saturio\DuckDB\Result\Metric;

class TimeMetric
{
    private int $phpNanoseconds = 0;
    private int $nativeNanoseconds = 0;

    private int $startedTime;
    private bool $currentContextIsPhp = true;


    public static function create(): TimeMetric
    {
        $metric = new TimeMetric();
        $metric->start();
        return $metric;
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