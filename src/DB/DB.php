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
        ?InstanceCache $instanceCache = null,
    ) {
        $this->db = $ffi->new('duckdb_database');

        if (null !== $instanceCache) {
            $result = $ffi->getOrCreateFromCache(
                $instanceCache->getInstanceCache(),
                $path,
                $ffi->addr($this->db),
                $config,
                $error,
            );
        }

        if (!isset($result) && null !== $config) {
            $result = $ffi->openExt(
                $path,
                $ffi->addr($this->db),
                NativeCDataConfiguration::fromConfiguration($config, $ffi)->getConfig(),
                $error,
            );
        }

        if (!isset($result)) {
            $result = $ffi->open($path, $ffi->addr($this->db));
        }

        if ($result === $ffi->error()) {
            $ffi->close($ffi->addr($this->db));
            throw new ConnectionException(sprintf('Cannot open database. %s', $error ?? 'Unknown error'));
        }
    }
}
