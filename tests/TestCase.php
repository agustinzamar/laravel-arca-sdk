<?php

namespace AgustinZamar\LaravelArcaSdk\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use AgustinZamar\LaravelArcaSdk\LaravelArcaSdkServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Copiar los archivos de keys/cert reales
        $tbStorageDir = storage_path('app/arca');

        if (!is_dir($tbStorageDir)) {
            mkdir($tbStorageDir, 0755, true);
        }

        copy(__DIR__ . '/../storage/app/arca/arca.key', $tbStorageDir . '/arca.key');
        copy(__DIR__ . '/../storage/app/arca/arca.crt', $tbStorageDir . '/arca.crt');

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'AgustinZamar\\LaravelArcaSdk\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelArcaSdkServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
