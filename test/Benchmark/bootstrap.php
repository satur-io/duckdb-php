<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

exec('php -f test/_data/createMeasurements.php 1000000000 test/_data/measurements.csv', $output, $code);

if (0 !== $code) {
    throw new RuntimeException(implode("\n", $output));
}
