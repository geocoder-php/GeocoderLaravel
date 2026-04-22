<?php

declare(strict_types=1);

use Geocoder\Laravel\Http\LaravelHttpClient;
use Geocoder\Provider\Chain\Chain;
use Geocoder\Provider\GeoPlugin\GeoPlugin;
use Geocoder\Provider\GoogleMaps\GoogleMaps;

return [
    'cache' => [

        /*
        |-----------------------------------------------------------------------
        | Cache Store
        |-----------------------------------------------------------------------
        |
        | Specify the cache store to use for caching. The value "null" will use
        | the default cache store specified in /config/cache.php file.
        |
        | Default: null
        |
        */

        'store' => null,

        /*
        |-----------------------------------------------------------------------
        | Cache Duration
        |-----------------------------------------------------------------------
        |
        | Specify the cache duration in seconds. The default approximates a
        | "forever" cache, but there are certain issues with Laravel's forever
        | caching methods that prevent us from using them in this project.
        |
        | Default: 9999999 (integer)
        |
        */

        'duration' => 9999999,
    ],

    /*
    |---------------------------------------------------------------------------
    | Providers
    |---------------------------------------------------------------------------
    |
    | Here you may specify any number of providers that should be used to
    | perform geocaching operations. The `chain` provider is special,
    | in that it can contain multiple providers that will be run in
    | the sequence listed, should the previous provider fail. By
    | default the first provider listed will be used, but you
    | can explicitly call subsequently listed providers by
    | alias: `app('geocoder')->using('google_maps')`.
    |
    | Providers that require a static factory method (e.g. `Nominatim::
    | withOpenStreetMapServer()`, `GoogleMaps::business()`) can be bound
    | in your AppServiceProvider — they'll be resolved via the container.
    | Configure the provider here with an empty args array:
    |
    |   // AppServiceProvider::register()
    |   $this->app->bind(Nominatim::class, fn ($app) => Nominatim::
    |       withOpenStreetMapServer($app->make(LaravelHttpClient::class),
    |       'MyApp/1.0'));
    |
    |   // config/geocoder.php
    |   Nominatim::class => [],
    |
    | Please consult the official Geocoder documentation for more info.
    | https://github.com/geocoder-php/Geocoder#providers
    |
    */
    'providers' => [
        Chain::class => [
            GoogleMaps::class => [
                env('GOOGLE_MAPS_LOCALE', 'us'),
                env('GOOGLE_MAPS_API_KEY'),
            ],
            GeoPlugin::class  => [],
        ],
    ],

    /*
    |---------------------------------------------------------------------------
    | Adapter
    |---------------------------------------------------------------------------
    |
    | The HTTP adapter to use when communicating with geocoding services. By
    | default this package ships a PSR-18 client that delegates to Laravel's
    | `Http` facade — this gives you `Http::fake()` in tests, native retry
    | and timeout configuration, and any HTTP middleware you've registered.
    |
    | Provide any class that implements `Psr\Http\Client\ClientInterface` to
    | swap in a different adapter (e.g., `Http\Client\Curl\Client` from
    | `php-http/curl-client`, which you would need to install separately).
    |
    | To pass constructor arguments (timeouts, proxies, client options, etc.)
    | use the array form `[Class => [args]]`. Arguments are forwarded to the
    | adapter's constructor — it's on you to match its signature. Named args
    | are supported: `[Class => ['timeout' => 10]]`.
    |
    | Default: LaravelHttpClient::class
    |
    | Examples:
    |   'adapter' => LaravelHttpClient::class,
    |   'adapter' => [LaravelHttpClient::class => ['timeout' => 10, 'retry' => [3, 100]]],
    |   'adapter' => [Http\Client\Curl\Client::class => [null, null, [CURLOPT_PROXY => '...']]],
    |
    */
    'adapter'  => LaravelHttpClient::class,

    /*
    |---------------------------------------------------------------------------
    | Reader
    |---------------------------------------------------------------------------
    |
    | You can specify a reader for specific providers, like GeoIp2, which
    | connect to a local file-database. The reader should be set to an
    | instance of the required reader class or an array containing the reader
    | class and arguments.
    |
    | Please consult the official Geocoder documentation for more info.
    | https://github.com/geocoder-php/geoip2-provider
    |
    | Default: null
    |
    | Example:
    |   'reader' => [
    |       WebService::class => [
    |           env('MAXMIND_USER_ID'),
    |           env('MAXMIND_LICENSE_KEY')
    |       ],
    |   ],
    |
    */
    'reader' => null,

];
