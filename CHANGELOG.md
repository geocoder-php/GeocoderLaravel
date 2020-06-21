# Geocoder for Laravel Changelog
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [4.3.4] - 2020-06-21
### Fixed
- non-caching declaration to only apply to current query.
- caching to take provider into account.

### Changed
- `getProvider()` method to no longer be deprecated, and instead return the
    currently set provider, or if none set, the first configured provider.

## [4.3.3] - 2020-06-20
### Added
- functionality to not cache requests by using `doNotCache()`.

## [4.3.0] - 2020-02-29
### Added
- Laravel 7 compatibility.

## [4.1.2] - 23 May 2019
### Fixed
- initialization of geocoder adapter.

## [4.0.21] - 3 Nov 2018
### Added
- `->toJson()` method when querying results.

## [4.0.10] - 1 Jul 2018
### Changed
- service provider to register singleton and alias in `register()` method.

## [4.0.9] - 28 May 2018
### Added
- class-name resolution from Service container, allowing for dependency
  injection.

## [4.0.8] - 25 Mar 2018
### Added
- work-around for missing `config_path()` function in Lumen.

## [4.0.7] - 25 Mar 2018
### Added
- optional dedicated cache store.
- hashed cache keys and hash collision prevention.
- custom cache store configuration instructions.

## [4.0.6] - 9 Feb 2018
### Added
- Laravel 5.6 compatibility.

## [4.0.5] - 14 Jan 2018
### Fixed
- loading of GeoIP2 provider from within Chain provider.

### Changed
- unit testing to use Orchstral Testbench.

## [4.0.4] - 27 Dec 2017
### Added
- environment variable configuration option in default config to set Google Maps Locale.
- documentation comments in configuration file.

### Changed
- composer dependency version constraints for Laravel to be within a specific range, instead of open-ended.

## [4.0.3] - 27 Oct 2017
### Fixed
- cache duration to work on 32-bit systems.
- geocoder to not cache if no results are provided.

## [4.0.2] - 2 Sep 2017
### Fixed
- erroneous method `getProvider()` and marked it as deprecated.

## [4.0.1] - 7 Aug 2017
### Fixed
- missing PSR-7 dependency.

## [4.0.0] - 3 Aug 2017
### Added
- Laravel 5.5 package auto-discovery.

### Fixed
- typo which caused cache to be in-effective.

### Changed
- implemented geocoder-php 4.0.0.
- version to 4.0.0 instead of 2.0.0 to maintain major version parity with
 parent package.
- composer dependencies to release versions.
- unit tests to pass.
- updated readme with some clarifying notes. May have to completely rewrite it
 if it ends up being unclear.

## [2.0.0-dev] - 23 Jun 2017
### Fixed
- failing Travis builds due TLS resolution issues by changing to a different
 geocoding provider that was failing said resolution during CURL requests.

### Changed
- build and coverage badges back to Travis and Coveralls

## [2.0.0-dev] - 18 Jun 2017
### Added
- compatibility with Geocoder 4.0-dev.
- caching to `geocodeQuery()` and `reverseQuery()` methods.

### Updated
- the geocoder `all()` method to be deprecated. Use `get()`.

## [1.1.0] - 17 Jun 2017
### Added
- caching functionality for `geocode()` and `reverse()` methods.
- `cache-duration` variable to geocoder config.

## [1.0.2] - 20 Mar 2017
### Added
- unit test for reverse-geocoding.

## [1.0.1] - 30 Jan 2017
### Removed
- minimum Laravel requirement of 5.3 (reverted back to 5.0, just in case it was working for someone, but only Laravel 5.3 and 5.4 are officially supported).

## [1.0.0] - 30 Jan 2017
### Changed
- minimum Laravel requirement to 5.3.

## [1.0.0-RC1] - 13 Oct 2016
### Added
- ability to dump results #16.
- ability to use multiple providers in addition to the chain provider #47.
- more integration tests.
- special aggregator that allows chaining of `geocode()` and other methods.

### Changed
- README documentation.
- to use Geocoder 3.3.x.
- namespace to `Geocoder\Laravel\...`.
- service provider to auto-load the facade.
- config file format.
- geocoding commands necessary to obtain results (must use `->all()`, `->get()`,
 or `->dump()`) after the respective command.
- the service provider architecture.

### Fixed
- MaxMindBinary Provider being instantiated with an Adapter #24.
- GeoIP2 Provider being instantiated with a generic Adapter.

## [0.6.0]
- TBD

## [0.5.0] - 11 Mar 2015
### Added
- code of conduct message.
- Laravel 5 compatibility [BC].

### Updated
- documentation.

## [0.4.1] - 23 Jun 2014
### Fixed
- the way to implode provider's arguments + unit tests.

## [0.4.0] - 13 Apr 2014
### Updated
- to use Geocoder 2.4.x.

## [0.3.0] - 13 Apr 2014
### Added
- support for Provider arguments (backwards-compatibility break).

## [0.2.0] - 16 Nov 2013
### Added
- config file.

### Updated
- to use Geocoder 2.3.x.
- to use singleton instead of share.
- tests.

## [0.1.0] - 16 Sep 2013
### Added
- badges.
- initial package.
