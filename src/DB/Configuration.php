<?php

declare(strict_types=1);

namespace Saturio\DuckDB\DB;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/** @implements IteratorAggregate<string, string> */
class Configuration implements IteratorAggregate
{
    private array $config = [];

    public function set(string $name, string $option): void
    {
        $this->config[$name] = $option;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->config);
    }
}
