<?php

declare(strict_types=1);

namespace Saturio\DuckDB\DB;

use Saturio\DuckDB\Exception\ConnectionException;
use Saturio\DuckDB\Exception\ErrorCreatingNewConfig;
use Saturio\DuckDB\Exception\InvalidConfigurationOption;
use Saturio\DuckDB\FFI\DuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class DB
{
    public NativeCData $db;

    /**
     * @throws ConnectionException
     * @throws ErrorCreatingNewConfig
     * @throws InvalidConfigurationOption
     */
    public function __construct(
        DuckDB $ffi,
        ?string $path = null,
        ?Configuration $config = null,
    ) {
        $this->db = $ffi->new('duckdb_database');

        if (null === $config) {
            $result = $ffi->open($path, $ffi->addr($this->db));
        } else {
            $result = $ffi->openExt(
                $path,
                $ffi->addr($this->db),
                NativeCDataConfiguration::fromConfiguration($config, $ffi)->getConfig(),
                $error,
            );
        }

        if ($result === $ffi->error()) {
            $ffi->close($ffi->addr($this->db));
            throw new ConnectionException(sprintf('Cannot open database. %s', $error ?? 'Unknown error'));
        }
    }
}
