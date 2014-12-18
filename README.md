Geocoder for Lavarel 4
======================

This package allows you to use [**Geocoder**](http://geocoder-php.org/Geocoder/)
in [**Laravel 4**](http://laravel.com/).

[![Latest StableVersion](https://poser.pugx.org/toin0u/geocoder-laravel/v/stable.png)](https://packagist.org/packages/toin0u/geocoder-laravel)
[![Total Downloads](https://poser.pugx.org/toin0u/geocoder-laravel/downloads.png)](https://packagist.org/packages/toin0u/geocoder-laravel)
[![Build Status](https://secure.travis-ci.org/geocoder-php/GeocoderLaravel.png)](http://travis-ci.org/geocoder-php/GeocoderLaravel)
[![Coverage Status](https://coveralls.io/repos/geocoder-php/GeocoderLaravel/badge.png)](https://coveralls.io/r/geocoder-php/GeocoderLaravel)


Installation
------------

It can be found on [Packagist](https://packagist.org/packages/toin0u/geocoder-laravel).
The recommended way is through [composer](http://getcomposer.org).

Edit `composer.json` and add:

```json
{
    "require": {
        "toin0u/geocoder-laravel": "@stable"
    }
}
```

**Protip:** you should browse the
[`toin0u/geocoder-laravel`](https://packagist.org/packages/toin0u/geocoder-laravel)
page to choose a stable version to use, avoid the `@stable` meta constraint.

And install dependencies:

```bash
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```


Usage
-----

Find the `providers` key in `app/config/app.php` and register the **Geocoder Service Provider**.

```php
'providers' => array(
    // ...

    'Toin0u\Geocoder\GeocoderServiceProvider',
)
```

Find the `aliases` key in `app/config/app.php` and register the **Geocoder Facade**.

```php
'aliases' => array(
    // ...

    'Geocoder' => 'Toin0u\Geocoder\GeocoderFacade',
)
```

Configuration
-------------

Publish and edit the configuration file

```bash
$ php artisan config:publish toin0u/geocoder-laravel
```

The service provider creates the following services:

* `geocoder`: the Geocoder instance.
* `geocoder.chain`: the chain provider used by Geocoder.
* `geocoder.adapter`: the HTTP adapter used to get data from remotes APIs.

By default, the `geocoder.chain` service contains `GoogleMapsProvider` and `FreeGeoIpProvider`.
The `geocoder.adapter` service uses the cURL adapter. Override these services to use the
adapter/providers you want by editing `app/config/packages/toin0u/geocoder-laravel/config.php`:

```php
return array(
    'providers' => array(
        'Geocoder\Provider\GoogleMapsProvider' => array('my-locale', 'my-region', $ssl = true, 'my-api-key'),
        'Geocoder\Provider\GoogleMapsBusinessProvider' => array(
            'my-client-id', 'my-api-key', 'my-locale', 'my-region', $ssl = true
        ),
        'Geocoder\Provider\CloudMadeProvider'  => array('my-api-key'),
        'Geocoder\Provider\FreeGeoIpProvider'  => null, // or array()
        // ...
    ),
    'adapter'  => 'Geocoder\HttpAdapter\CurlHttpAdapter'
);
```

NB: As you can see the array value of the provider is the constructor arguments.

See [the Geocoder documentation](http://geocoder-php.org/Geocoder/) for a list of available adapters and providers.


Example with Facade
-------------------

```php
<?php

// ...
try {
    $geocode = Geocoder::geocode('10 rue Gambetta, Paris, France');
    // The GoogleMapsProvider will return a result
    var_dump($geocode);
} catch (\Exception $e) {
    // No exception will be thrown here
    echo $e->getMessage();
}
```


Changelog
---------

[See the CHANGELOG file](https://github.com/geocoder-php/GeocoderLaravel/blob/master/CHANGELOG.md)


Support
-------

[Please open an issue on GitHub](https://github.com/geocoder-php/GeocoderLaravel/issues)


Contributor Code of Conduct
---------------------------

Please note that this project is released with a Contributor Code of Conduct.
By participating in this project you agree to abide by its terms.


License
-------

GeocoderLaravel is released under the MIT License. See the bundled
[LICENSE](https://github.com/geocoder-php/GeocoderLaravel/blob/master/LICENSE)
file for details.
