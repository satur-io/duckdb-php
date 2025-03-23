<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

trait CollectMetrics
{
    protected bool $collectMetrics;

    protected function initCollectMetrics(): void
    {
        $this->collectMetrics = duckdb_php_collect_metrics();
    }
}
