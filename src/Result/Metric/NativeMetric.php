<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result\Metric;

use Saturio\DuckDB\DB\Connection;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class NativeMetric
{
    public static function getLatency(
        FFIDuckDB $ffi,
        Connection $conn,
    ): float {
        $profilingInfo = $ffi->profilingInfo($conn->connection);

        if (null === $profilingInfo) {
            return 0.0;
        }

        $latencyValue = $ffi->profilingInfoGetValue($profilingInfo, 'LATENCY');
        if (null !== $latencyValue) {
            return self::metricSeconds($ffi, $latencyValue);
        }

        $childCount = $ffi->profilingInfoChildCount($profilingInfo);
        for ($index = 0; $index < $childCount; ++$index) {
            $child = $ffi->profilingInfoChild($profilingInfo, $index);
            if (null === $ffi->profilingInfoGetValue($child, 'sql')) {
                continue;
            }

            $totalTime = $ffi->profilingInfoGetValue($child, 'total_time');
            if (null !== $totalTime) {
                return self::metricSeconds($ffi, $totalTime);
            }
        }

        return 0.0;
    }

    private static function metricSeconds(FFIDuckDB $ffi, NativeCData $value): float
    {
        $asString = $ffi->getVarchar($value);

        return is_numeric($asString) ? (float) $asString : $ffi->getDouble($value);
    }
}
