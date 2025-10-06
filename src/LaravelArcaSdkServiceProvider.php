<?php

namespace AgustinZamar\LaravelArcaSdk;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->name('arca-sdk')
            ->hasConfigFile()
            ->hasViews();
    }
}
