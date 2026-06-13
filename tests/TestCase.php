<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $mpdfTempDir = storage_path('app/temp/mpdf');

        if (! is_dir($mpdfTempDir)) {
            mkdir($mpdfTempDir, 0755, true);
        }
    }
}
