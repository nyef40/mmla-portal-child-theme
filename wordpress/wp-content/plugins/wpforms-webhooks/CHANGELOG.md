# Changelog
All notable changes to this project will be documented in this file, formatted via [this recommendation](https://keepachangelog.com/).

## [1.2.0] - 2022-05-26
### IMPORTANT
- Support for WordPress 5.1 has been discontinued. If you are running WordPress 5.1, you MUST upgrade WordPress before installing the new WPForms Webhooks. Failure to do that will disable the new WPForms Webhooks functionality.

### Changed
- Minimum WPForms version supported is 1.7.4.2.
- Extend hook arguments: make it possible to use formatted field values.

## [1.1.0] - 2021-10-28
### Added
- Process smart tags inside Request Headers and Body fields.
- Compatibility with WPForms 1.6.8 and the updated Form Builder.

### Changed
- Improved compatibility with jQuery 3.5 and no jQuery Migrate plugin.
- Improved compatibility with WordPress 5.9 and PHP 8.1.

### Fixed
- Compatibility with WordPress Multisite installations.
- Improve the way webhooks are disabled and displayed when disabled.
- Incorrect width of setting fields in Safari.
- Issue with saving settings.

## [1.0.0] - 2020-07-08
- Initial release.
