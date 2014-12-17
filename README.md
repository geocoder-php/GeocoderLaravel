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

As contributors and maintainers of this project, we pledge to respect all people
who contribute through reporting issues, posting feature requests, updating
documentation, submitting pull requests or patches, and other activities.

We are committed to making participation in this project a harassment-free
experience for everyone, regardless of level of experience, gender, gender
identity and expression, sexual orientation, disability, personal appearance,
body size, race, age, or religion.

Examples of unacceptable behavior by participants include the use of sexual
language or imagery, derogatory comments or personal attacks, trolling, public
or private harassment, insults, or other unprofessional conduct.

Project maintainers have the right and responsibility to remove, edit, or reject
comments, commits, code, wiki edits, issues, and other contributions that are
not aligned to this Code of Conduct. Project maintainers who do not follow the
Code of Conduct may be removed from the project team.

Instances of abusive, harassing, or otherwise unacceptable behavior may be
reported by opening an issue or contacting one or more of the project
maintainers.

This Code of Conduct is adapted from the [Contributor
Covenant](http:contributor-covenant.org), version 1.0.0, available at
[http://contributor-covenant.org/version/1/0/0/](http://contributor-covenant.org/version/1/0/0/)


License
-------

GeocoderLaravel is released under the MIT License. See the bundled
[LICENSE](https://github.com/geocoder-php/GeocoderLaravel/blob/master/LICENSE)
file for details.
