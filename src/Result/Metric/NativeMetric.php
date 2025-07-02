<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result\Metric;

use Saturio\DuckDB\DB\Connection;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;

class NativeMetric
{
    public static function getLatency(
        FFIDuckDB $ffi,
        Connection $conn,
    ): float {
        $profilingInfo = $ffi->profilingInfo($conn->connection);

        if (null === $profilingInfo) {
            return 0;
        }

        $latencyValue = $ffi->profilingInfoGetValue($profilingInfo, 'LATENCY');

        return $ffi->getDouble($latencyValue);
    }
}
