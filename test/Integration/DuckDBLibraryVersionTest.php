<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Saturio\DuckDB\CLib\Version;
use Saturio\DuckDB\Exception\WrongLibraryVersionException;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;

class DuckDBLibraryVersionTest extends TestCase
{
    private ?string $previousVersion = null;

    protected function setUp(): void
    {
        $this->previousVersion = getenv(Version::ENV_KEY) ?: null;
    }

    protected function tearDown(): void
    {
        $this->restoreEnv();
        $this->resetFFI();
    }

    public function testThrowsWhenVersionDoesNotMatch(): void
    {
        $supported = Version::supported();
        $current = Version::resolve();

        // Ensure the FFI instance is initialized with the current version.
        new FFIDuckDB();

        $other = null;
        foreach ($supported as $version) {
            if ($version !== $current) {
                $other = $version;
                break;
            }
        }

        if ($other === null) {
            $this->markTestSkipped('Only one supported version available.');
        }

        putenv(Version::ENV_KEY.'='.$other);

        $this->expectException(WrongLibraryVersionException::class);
        new FFIDuckDB();
    }

    private function restoreEnv(): void
    {
        if ($this->previousVersion === null) {
            putenv(Version::ENV_KEY);
        } else {
            putenv(Version::ENV_KEY.'='.$this->previousVersion);
        }
    }

    private function resetFFI(): void
    {
        $property = new ReflectionProperty(FFIDuckDB::class, 'ffi');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }
}
