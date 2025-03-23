<?php


require __DIR__ . '/../vendor/autoload.php';

use Saturio\DuckDB\DuckDB;

DuckDB::sql(
    'SELECT "Reporting Year", avg("Gas Produced, MCF") as "AVG Gas Produced" 
                FROM "https://github.com/plotly/datasets/raw/refs/heads/master/oil-and-gas.parquet" 
                WHERE "Reporting Year" BETWEEN 1985 AND 1990
                GROUP BY "Reporting Year";'
)->print();
