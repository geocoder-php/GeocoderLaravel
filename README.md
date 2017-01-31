[![Latest StableVersion](https://poser.pugx.org/toin0u/geocoder-laravel/v/stable.png)](https://packagist.org/packages/toin0u/geocoder-laravel)
[![Total Downloads](https://poser.pugx.org/toin0u/geocoder-laravel/downloads.png)](https://packagist.org/packages/toin0u/geocoder-laravel)
[![Build Status](https://ci.genealabs.com/build-status/image/1)](https://ci.genealabs.com/build-status/view/1)
[Code Coverate Report](https://ci.genealabs.com/coverage/1)

# Geocoder for Lavarel

> If you still use **Laravel 4**, please check out the `0.4.x` branch
 [here](https://github.com/geocoder-php/GeocoderLaravel/tree/0.4.x).

**Version 1.0.0 is a backwards-compatibility-breaking update. Please review
 this documentation, especially the _Usage_ section before installing.**

This package allows you to use [**Geocoder**](http://geocoder-php.org/Geocoder/)
 in [**Laravel 5**](http://laravel.com/).

## Installation
1. Install the package via composer:
  ```sh
  composer require toin0u/geocoder-laravel
  ```
  _Once 1.0.0 is stable, we will update this command to reflect that. In the interest of getting it out and into your hands, a temporary RC build is best._

2. Find the `providers` array key in `config/app.php` and register the **Geocoder
 Service Provider**:
  ```php
  // 'providers' => [
      Geocoder\Laravel\Providers\GeocoderService::class,
  // ];
  ```

## Configuration
Pay special attention to the language and region values if you are using them.
 For example, the GoogleMaps provider uses TLDs for region values, and the
 following for language values: https://developers.google.com/maps/faq#languagesupport.

Further, a special note on the GoogleMaps provider: if you are using an API key,
 you must also use set HTTPS to true. (Best is to leave it true always, unless
 there is a special requirement not to.)

See the [Geocoder documentation](http://geocoder-php.org/Geocoder/) for a list
 of available adapters and providers.

### Default Settings
By default, the configuration specifies a Chain Provider as the first provider,
 containing GoogleMaps and FreeGeoIp providers. The first to return a result
 will be returned. After the Chain Provider, we have added the BingMaps provider
 for use in specific situations (providers contained in the Chain provider will
 be run by default, providers not in the Chain provider need to be called
 explicitly). The second GoogleMaps Provider outside of the Chain Provider is
 there just to illustrate this point (and is used by the PHPUnit tests).
```php
return [
    'providers' => [
        Chain::class => [
            GoogleMaps::class => [
                'en',
                'us',
                true,
                env('GOOGLE_MAPS_API_KEY'),
            ],
            FreeGeoIp::class  => [],
        ],
        BingMaps::class => [
            'en-US',
            env('BING_MAPS_API_KEY'),
        ],
        GoogleMaps::class => [
            'en',
            'us',
            true,
            env('GOOGLE_MAPS_API_KEY'),
        ],
    ],
    'adapter'  => CurlHttpAdapter::class,
];
```

### Customization
If you would like to make changes to the default configuration, publish and
 edit the configuration file:
```sh
php artisan vendor:publish --provider="Geocoder\Laravel\GeocoderServiceProvider" --tag="config"
```

## Usage
The service provider initializes the `geocoder` service, accessible via the
 facade `Geocoder::...` or the application helper `app('geocoder')->...`.

### Geocoding Addresses
#### Get Collection of Addresses
```php
app('geocoder')->geocode('Los Angeles, CA')->get();
```

#### Get Array of Addresses
```php
app('geocoder')->geocode('Los Angeles, CA')->all();
```

#### Reverse-Geocoding
```php
app('geocoder')->reverse(43.882587,-103.454067)->get();
```

#### Dumping Results
```php
app('geocoder')->geocode('Los Angeles, CA')->dump('kml');
```

## Changelog
https://github.com/geocoder-php/GeocoderLaravel/blob/master/CHANGELOG.md

## Support
If you are experiencing difficulties, please please open an issue on GitHub:
 https://github.com/geocoder-php/GeocoderLaravel/issues.

## Contributor Code of Conduct
Please note that this project is released with a
 [Contributor Code of Conduct](https://github.com/geocoder-php/Geocoder#contributor-code-of-conduct).
 By participating in this project you agree to abide by its terms.

## License
GeocoderLaravel is released under the MIT License. See the bundled
 [LICENSE](https://github.com/geocoder-php/GeocoderLaravel/blob/master/LICENSE)
 file for details.
