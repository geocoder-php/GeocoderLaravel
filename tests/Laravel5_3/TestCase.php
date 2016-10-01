<?php namespace Toin0u\GeocoderLaravel\Tests\Laravel5_3;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class TestCase extends BaseTestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;

    protected $baseUrl = 'http://localhost';

    public function createApplication() : Application
    {
        $app = require __DIR__ . '/../../../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
