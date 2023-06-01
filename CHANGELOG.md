# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
-

## [4.2.3] - 2023-06-01

### Commits

- Switch from `pronamic/wp-deployer` to `pronamic/pronamic-cli`. ([895a744](https://github.com/pronamic/wp-pronamic-pay-give/commit/895a7444e7222f3c323660365ac9672b431069ff))
- Updated .gitattributes ([ccbe6b0](https://github.com/pronamic/wp-pronamic-pay-give/commit/ccbe6b08a361c08fa5447302aeb6a14101600e2c))

Full set of changes: [`4.2.2...4.2.3`][4.2.3]

[4.2.3]: https://github.com/pronamic/wp-pronamic-pay-give/compare/v4.2.2...v4.2.3

## [4.2.2] - 2023-03-27

### Commits

- Set Composer type to WordPress plugin. ([b0b5355](https://github.com/pronamic/wp-pronamic-pay-give/commit/b0b535576964d5f190b55b83e585e87ec46522e8))
- Updated .gitattributes ([7a84c42](https://github.com/pronamic/wp-pronamic-pay-give/commit/7a84c42f82627eb2d9575e485e3ad871432b7d41))
- Requires PHP: 7.4. ([5bde3a8](https://github.com/pronamic/wp-pronamic-pay-give/commit/5bde3a84504a006ec2b596375296cf240a18b7c1))

Full set of changes: [`4.2.1...4.2.2`][4.2.2]

[4.2.2]: https://github.com/pronamic/wp-pronamic-pay-give/compare/v4.2.1...v4.2.2

## [4.2.1] - 2023-01-31
### Composer

- Changed `php` from `>=8.0` to `>=7.4`.
Full set of changes: [`4.2.0...4.2.1`][4.2.1]

[4.2.1]: https://github.com/pronamic/wp-pronamic-pay-give/compare/v4.2.0...v4.2.1

## [4.2.0] - 2022-12-23

### Commits

- Added "Requires Plugins" header. ([f81a66b](https://github.com/pronamic/wp-pronamic-pay-give/commit/f81a66bb75a1f0b1b419bfeacc34e9ced6b1f72c))

### Composer

- Changed `php` from `>=5.6.20` to `>=8.0`.
- Changed `wp-pay/core` from `^4.4` to `v4.6.0`.
	Release notes: https://github.com/pronamic/wp-pay-core/releases/tag/v4.1.1
Full set of changes: [`4.1.1...4.2.0`][4.2.0]

[4.2.0]: https://github.com/pronamic/wp-pronamic-pay-give/compare/v4.1.1...v4.2.0

## [4.1.1] - 2022-09-27
- Update to `wp-pay/core` version `^4.4`.

## [4.1.0] - 2022-09-26
- Updated for new payment methods and fields registration.

## [4.0.0] - 2022-01-10
### Changed
- Updated to https://github.com/pronamic/wp-pay-core/releases/tag/4.0.0.
- Only enable payment gateways with valid configuration (fixes wp-pay-extensions/give#3).

## [3.0.0] - 2021-08-05
- Updated to `pronamic/wp-pay-core`  version `3.0.0`.
- Updated to `pronamic/wp-money`  version `2.0.0`.
- Changed `TaxedMoney` to `Money`, no tax info.
- Switched to `pronamic/wp-coding-standards`.
- Simplified gateway registration for all active payment methods.

## [2.2.1] - 2021-04-26
- Happy 2021.

## [2.2.0] - 2021-01-14
- Removed payment data class.

## [2.1.1] - 2020-04-03
- Updated integration dependencies.
- Set plugin integration name.

## [2.1.0] - 2020-03-19
- Extension extends abstract plugin integration.

## [2.0.4] - 2020-01-03
- Fix inaccessible property error.

## [2.0.3] - 2019-12-22
- Improved error handling with exceptions.
- Updated gateway settings.
- Updated payment status class name.

## [2.0.2] - 2019-08-26
- Updated packages.

## [2.0.1] - 2018-12-12
- Fix using default gateway setting.
- Update item methods in payment data.

## [2.0.0] - 2018-05-14
- Switched to PHP namespaces.

## [1.0.6] - 2017-09-13
- Implemented `get_first_name()` and `get_last_name()`.

## [1.0.5] - 2017-01-25
- Added filter for payment source description and URL.

## [1.0.4] - 2016-11-16
- Display payment input fields also if guest donations are not allowed and registration and/or login forms are displayed.

## [1.0.3] - 2016-10-20
- Use 'donation' instead of 'transaction' in transaction description.
- Added Pronamic gateway usage clarification.
- Added transaction description setting.
- Use new Bancontact label and constant.

## [1.0.2] - 2016-06-08
- Fixed text domain `give` = `pronamic_ideal`.

## [1.0.1] - 2016-04-12
- Improved error handling.
- Refactored class construct gateways.
- Empty unused get URL functions.

## 1.0.0 - 2016-03-23
- First release.

[unreleased]: https://github.com/wp-pay-extensions/give/compare/4.1.1...HEAD
[4.1.1]: https://github.com/pronamic/wp-pronamic-pay-give/compare/4.1.0...4.1.1
[4.1.0]: https://github.com/pronamic/wp-pronamic-pay-give/compare/4.0.0...4.1.0
[4.0.0]: https://github.com/wp-pay-extensions/give/compare/3.0.0...4.0.0
[3.0.0]: https://github.com/wp-pay-extensions/give/compare/2.2.1...3.0.0
[2.2.1]: https://github.com/wp-pay-extensions/give/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/wp-pay-extensions/give/compare/2.1.1...2.2.0
[2.1.1]: https://github.com/wp-pay-extensions/give/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/wp-pay-extensions/give/compare/2.0.4...2.1.0
[2.0.4]: https://github.com/wp-pay-extensions/give/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/wp-pay-extensions/give/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/wp-pay-extensions/give/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/wp-pay-extensions/give/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/wp-pay-extensions/give/compare/1.0.6...2.0.0
[1.0.6]: https://github.com/wp-pay-extensions/give/compare/1.0.5...1.0.6
[1.0.5]: https://github.com/wp-pay-extensions/give/compare/1.0.4...1.0.5
[1.0.4]: https://github.com/wp-pay-extensions/give/compare/1.0.3...1.0.4
[1.0.3]: https://github.com/wp-pay-extensions/give/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/wp-pay-extensions/give/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/wp-pay-extensions/give/compare/1.0.0...1.0.1
