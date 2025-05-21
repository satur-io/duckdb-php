<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

if (!is_dir('.phpbench/samples')) {
    mkdir('.phpbench/samples');
}

$files = [
    '.phpbench/samples/oil-and-gas.parquet' => 'https://github.com/plotly/datasets/raw/refs/heads/master/oil-and-gas.parquet',
    '.phpbench/samples/dutch_railway_network.duckdb' => 'https://blobs.duckdb.org/data/dutch_railway_network.duckdb',
];

foreach ($files as $file => $url) {
    if (file_exists($file)) {
        return;
    }

    $ch = curl_init($url);

    $fp = fopen($file, 'w+');

    curl_setopt_array($ch, [
        CURLOPT_FILE => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_FAILONERROR => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $success = curl_exec($ch);

    fclose($fp);

    if (!$success) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException("Error downloading {$url}: {$error}");
    }

    curl_close($ch);
}
