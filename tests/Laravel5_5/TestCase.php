<?php namespace Geocoder\Laravel\Tests\Laravel5_5;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
}
