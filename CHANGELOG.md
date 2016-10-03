# Geocoder for Laravel Changelog
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [0.7.0] - 1 Oct 2016
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
