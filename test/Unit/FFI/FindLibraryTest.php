<?php

declare(strict_types=1);

namespace Unit\FFI;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\Exception\MissedLibraryException;
use Saturio\DuckDB\FFI\FindLibrary;

class FindLibraryTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('DUCKDB_PHP_PATH');
    }

    public function testDefaultPathEndsWithLib(): void
    {
        $defaultPath = FindLibrary::defaultPath();

        $this->assertNotEmpty($defaultPath);
        $this->assertStringEndsWith('lib', $defaultPath);
    }

    public function testHeaderPathEndsWithHeaderFile(): void
    {
        $headerPath = FindLibrary::headerPath();

        $this->assertNotEmpty($headerPath);
        $this->assertStringEndsWith('duckdb-ffi.h', $headerPath);
    }

    public function testLibPathReturnsPlatformSpecificFile(): void
    {
        $libPath = FindLibrary::libPath();

        $this->assertNotEmpty($libPath);
        $os = php_uname('s');
        $expectedExtension = match ($os) {
            'Windows NT' => 'duckdb.dll',
            'Darwin' => 'libduckdb.dylib',
            default => 'libduckdb.so',
        };

        $this->assertStringEndsWith($expectedExtension, $libPath);
    }

    public function testCustomDuckdbPathViaEnv(): void
    {
        $customDir = '/custom/duckdb/path';
        putenv("DUCKDB_PHP_PATH={$customDir}");

        $this->assertSame(
            implode(DIRECTORY_SEPARATOR, [$customDir, 'duckdb-ffi.h']),
            FindLibrary::headerPath()
        );

        $os = php_uname('s');
        $expectedFile = match ($os) {
            'Windows NT' => 'duckdb.dll',
            'Darwin' => 'libduckdb.dylib',
            default => 'libduckdb.so',
        };

        $this->assertSame(
            implode(DIRECTORY_SEPARATOR, [$customDir, $expectedFile]),
            FindLibrary::libPath()
        );
    }

    public function testHeaderAndLibraryThrowsWhenFilesDoNotExist(): void
    {
        putenv('DUCKDB_PHP_PATH=/nonexistent/path/' . uniqid());

        $this->expectException(MissedLibraryException::class);
        FindLibrary::headerAndLibrary();
    }
}
