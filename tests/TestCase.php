<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use ZeroToProd\LaravelOpenapi\ValidatesSchema;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;
    use ValidatesSchema;

    private static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! self::$migrated) {
            Artisan::call('migrate:fresh');
            self::$migrated = true;
        }
    }
}
