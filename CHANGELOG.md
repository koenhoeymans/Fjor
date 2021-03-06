# Fjor Changelog

## [Unreleased]
### Changed
- `addParam()` doesn't require an array with parameters to be injected. Parameters
  are now specified the way they should be injected.

## [0.3.0] - 2018-08-07
### Changed

- Require PHPUnit 7.*
- Minimum PHP 7.2 required.
- Changelog format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

### fixed

- Optional attributes no longer are passed `null`


## older changes

*   0.2.0

    *   Reorganized project directory structure.
    *   Conform to PSR-3 and PSR-4.
    *   Added changes for QualityCheck library to work.

*	0.1.5

	*	Updated to `Epa` version 0.2.0.

*	0.1.4

	*	Fixed bug where `AfterNew` event is thrown when object is requested
		but not when object is created.

*	0.1.3

	*	Method injection can be specified for parentclasses.

*	0.1.2

	*	More easy to instantiate using utility static method.
	*	Pluggable (`AfterNew` event).

*	0.1.1

	*	Now installable through Composer.

*	0.1.0

	*	First release.