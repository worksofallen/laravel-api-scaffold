<?php

declare(strict_types=1);

namespace Worksofallen\LaravelCommand;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Worksofallen\LaravelCommand\Console\Commands\MakeApiCommand;

class LaravelCommandServiceProvider extends PackageServiceProvider
{
    protected $commands = [
        MakeApiCommand::class,
    ];

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-api-scaffold')
            ->hasCommands(MakeApiCommand::class);
    }

    public function boot()
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }
}
