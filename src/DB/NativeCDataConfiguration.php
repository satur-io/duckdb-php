<?php

declare(strict_types=1);

namespace Saturio\DuckDB\DB;

use Saturio\DuckDB\Exception\ErrorCreatingNewConfig;
use Saturio\DuckDB\Exception\InvalidConfigurationOption;
use Saturio\DuckDB\FFI\DuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class NativeCDataConfiguration
{
    private NativeCData $config;

    /**
     * @throws ErrorCreatingNewConfig
     */
    public function __construct(
        private readonly DuckDB $ffi,
    ) {
        $this->config = $this->ffi->new('duckdb_config');
        if ($ffi->createConfig($this->ffi->addr($this->config)) === $ffi->error()) {
            throw new ErrorCreatingNewConfig('Could not create new configuration');
        }
    }

    /**
     * @throws InvalidConfigurationOption
     */
    public function set(string $name, string $option): void
    {
        if ($this->ffi->setConfig($this->config, $name, $option) === $this->ffi->error()) {
            throw new InvalidConfigurationOption('Could not set new configuration');
        }
    }

    /**
     * @throws ErrorCreatingNewConfig
     * @throws InvalidConfigurationOption
     */
    public static function fromConfiguration(Configuration $config, DuckDB $ffi): self
    {
        $nativeConfiguration = new self($ffi);
        foreach ($config as $name => $option) {
            $nativeConfiguration->set($name, $option);
        }

        return $nativeConfiguration;
    }

    public function getConfig(): NativeCData
    {
        return $this->config;
    }
}
