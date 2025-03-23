<?php

if (!empty(getenv('DUCKDB_PHP_COLLECT_METRICS'))) {
    $GLOBALS['duckdb_metrics'] = [];
    register_shutdown_function(function () {
        foreach ($GLOBALS['duckdb_metrics'] as $group => $metric) {
            fwrite(STDERR, sprintf("%s\t%s\t%s\n", $group, 'total', $metric['time']));
            fwrite(STDERR, sprintf("%s\t%s\t%s\n", $group, 'min', $metric['min']));
            fwrite(STDERR, sprintf("%s\t%s\t%s\n", $group, 'max', $metric['max']));
            fwrite(STDERR, sprintf("%s\t%s\t%s\n", $group, 'mean', $metric['time'] / $metric['count']));
            fwrite(STDERR, sprintf("%s\t%s\t%s\n", $group, 'first', $metric['first']));
            fwrite(STDERR, sprintf("%s\t%s\t%s\n", $group, 'count', $metric['count']));
            fwrite(STDERR, "\n");
        }
    });
}

function duckdb_php_collect_metrics(): bool
{
    static $collect = !empty(getenv('DUCKDB_PHP_COLLECT_METRICS'));
    return $collect;
}

function collect_time(?object &$context, string $group): void
{
    $start = hrtime(true);
    $context ??= new readonly class($start, $group) {
        public function __construct(private int $start, private string $group) {}
        public function __destruct()
        {
            $total = hrtime(true) - $this->start;
            !array_key_exists($this->group, $GLOBALS['duckdb_metrics'])
            ?
                $GLOBALS['duckdb_metrics'][$this->group] = [
                    'time' => $total,
                    'min' => $total,
                    'max' => $total,
                    'count' => 1,
                    'first' => $total,
                ]
            :
                $GLOBALS['duckdb_metrics'][$this->group] = [
                    'time' => $GLOBALS['duckdb_metrics'][$this->group]['time'] + $total,
                    'min' => min($GLOBALS['duckdb_metrics'][$this->group]['min'], $total),
                    'max' => max($GLOBALS['duckdb_metrics'][$this->group]['max'], $total),
                    'count' => ++$GLOBALS['duckdb_metrics'][$this->group]['count'],
                    'first' => $GLOBALS['duckdb_metrics'][$this->group]['first'],
                ];
        }
    };
}
