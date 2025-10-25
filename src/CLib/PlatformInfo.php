<?php

declare(strict_types=1);

namespace Saturio\DuckDB\CLib;

use Saturio\DuckDB\Exception\NotSupportedException;

class PlatformInfo
{
    /**
     * @throws NotSupportedException
     */
    public static function getPlatformInfo(): array
    {
        $os = php_uname('s');
        $machine = php_uname('m');

        return match ($os) {
            'Windows NT' => match ($machine) {
                'AMD64', 'x64' => ['platform' => 'windows-amd64', 'file' => 'duckdb.dll'],
                'ARM64' => ['platform' => 'windows-arm64', 'file' => 'duckdb.dll'],
                default => throw new NotSupportedException("Unsupported OS: {$os}-{$machine}"),
            },
            'Linux' => match ($machine) {
                'x86_64' => ['platform' => 'linux-amd64', 'file' => 'libduckdb.so'],
                'aarch64' => ['platform' => 'linux-arm64', 'file' => 'libduckdb.so'],
                default => throw new NotSupportedException("Unsupported OS: {$os}-{$machine}"),
            },
            'Darwin' => ['platform' => 'osx-universal', 'file' => 'libduckdb.dylib'],
            default => throw new NotSupportedException("Unsupported OS: {$os}-{$machine}"),
        };
    }
}
