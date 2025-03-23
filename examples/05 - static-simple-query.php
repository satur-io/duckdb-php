<?php

require __DIR__ . '/../vendor/autoload.php';

use Saturio\DuckDB\DuckDB;

DuckDB::sql("SELECT 'quack' as my_column")->print();
