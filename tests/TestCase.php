<?php

declare(strict_types=1);

namespace Worksofallen\LaravelCommand\Tests;

use Worksofallen\LaravelCommand\LaravelCommandServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelCommandServiceProvider::class,
        ];
    }
}
