<?php

declare(strict_types=1);

namespace Integration\Helper;

trait IntegrationTestTrait
{
    protected function loadNativeClasses(): void
    {
        // For integration tests we want the real native classes
        include_once __DIR__.'/../../../aliases-prod.php';
    }

    public static function setUpBeforeClass(): void
    {
        include_once __DIR__.'/../../../aliases-prod.php';
    }
}
