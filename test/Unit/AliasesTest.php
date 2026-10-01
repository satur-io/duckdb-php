<?php

declare(strict_types=1);

namespace Unit;

use FFI;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AliasesTest extends TestCase
{
    /**
     * aliases.php runs again when the aliases already exist, e.g. when
     * OPcache preloading declared them and Composer's autoloader includes
     * the file on every request. It must not emit a warning then.
     */
    public function testRequiringAliasesTwiceDoesNotWarn(): void
    {
        require __DIR__.'/../../aliases.php';

        $this->assertSame(FFI::class, (new ReflectionClass('Saturio\DuckDB\Native\FFI'))->getName());
        $this->assertSame(\FFI\CData::class, (new ReflectionClass('Saturio\DuckDB\Native\FFI\CData'))->getName());
    }
}
