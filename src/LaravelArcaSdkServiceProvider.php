<?php

namespace AgustinZamar\LaravelArcaSdk;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use AgustinZamar\Skeleton\Commands\SkeletonCommand;

class LaravelArcaSdkServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('Laravel ARCA SDK')
            ->hasConfigFile('laravel-arca-sdk');
//            ->hasViews()
//            ->hasMigration('create_migration_table_name_table')
//            ->hasCommand(SkeletonCommand::class);
    }
}
