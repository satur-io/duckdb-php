<?php

declare(strict_types=1);

namespace Unit\FFI;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Saturio\DuckDB\CLib\PlatformInfo;
use Saturio\DuckDB\Exception\MissedLibraryException;
use Saturio\DuckDB\FFI\FindLibrary;

class FindLibraryTest extends TestCase
{
    private ?string $previousDuckdbPath = null;
    private array $tempDirs = [];

    protected function setUp(): void
    {
        $this->previousDuckdbPath = getenv('DUCKDB_PHP_PATH') ?: null;
    }

    protected function tearDown(): void
    {
        $this->restoreEnv();
        foreach ($this->tempDirs as $dir) {
            $this->removeDir($dir);
        }
        $this->tempDirs = [];
    }

    public function testDefaultPathIncludesVersion(): void
    {
        $path = FindLibrary::defaultPath('1.4.2');

        $this->assertStringEndsWith(DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'1.4.2', $path);
    }

    public function testHeaderAndLibraryUsesConfiguredPath(): void
    {
        $tmp = $this->createTempLibDir();
        putenv('DUCKDB_PHP_PATH='.$tmp);

        [$header, $lib] = FindLibrary::headerAndLibrary('1.4.0');

        $this->assertSame($tmp.DIRECTORY_SEPARATOR.'duckdb-ffi.h', $header);
        $this->assertSame($tmp.DIRECTORY_SEPARATOR.PlatformInfo::getPlatformInfo()['file'], $lib);
    }

    public function testHeaderAndLibraryThrowsWhenConfiguredPathMissing(): void
    {
        $tmp = $this->createTempDir();
        putenv('DUCKDB_PHP_PATH='.$tmp);

        $this->expectException(MissedLibraryException::class);
        FindLibrary::headerAndLibrary('1.4.0');
    }

    private function createTempDir(): string
    {
        $dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .'duckdb-php-test-'.uniqid('', true);

        if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Failed to create temp directory: %s', $dir));
        }

        $this->tempDirs[] = $dir;

        return $dir;
    }

    private function createTempLibDir(): string
    {
        $dir = $this->createTempDir();
        $libFile = PlatformInfo::getPlatformInfo()['file'];

        file_put_contents($dir.DIRECTORY_SEPARATOR.'duckdb-ffi.h', '// test header');
        file_put_contents($dir.DIRECTORY_SEPARATOR.$libFile, '');

        return $dir;
    }

    private function restoreEnv(): void
    {
        if ($this->previousDuckdbPath === null) {
            putenv('DUCKDB_PHP_PATH');
        } else {
            putenv('DUCKDB_PHP_PATH='.$this->previousDuckdbPath);
        }
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }
            unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
