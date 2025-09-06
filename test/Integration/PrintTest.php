<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\DuckDB;

class PrintTest extends TestCase
{
    public function testPrint(): void
    {
        ob_start();
        DuckDB::sql("SELECT 'quack' as my_column")->print();
        $printResult = ob_get_contents();
        ob_end_clean();

        self::assertStringContainsString(
            'my_column',
            $printResult,
        );

        self::assertStringContainsString(
            'quack',
            $printResult,
        );
    }
}
