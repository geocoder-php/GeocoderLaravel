<?php namespace Geocoder\Laravel\Tests\Laravel5_5;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../vendor/laravel/laravel/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        config([
            'geocoder' => include(__DIR__ . '/../assets/testConfig.php'),
        ]);

        return $app;
    }
}
