#!/bin/bash
export CURRENT_BRANCH=$(git branch --show-current)
git switch main
git checkout ${CURRENT_BRANCH} test/Benchmark phpbench-local.json preload.php
composer dump-autoload
vendor/bin/phpbench run --config=phpbench-local.json --tag=main --report=duckdb_benchmark_report
git switch ${CURRENT_BRANCH}
composer dump-autoload
vendor/bin/phpbench run --config=phpbench-local.json --ref=main --report=duckdb_benchmark_report
