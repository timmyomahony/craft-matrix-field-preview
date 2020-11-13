# Matrix Field Preview Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.0.0 - 2020-04-01

### Added

- Initial release

## 1.0.1 - 2020-05-23

### Changed

- Fixed issue with the JavaScript asset bundle running on non-cp requests

## 1.0.2 - 2020-06-03

### Changed

- Fixed issues with default preview images

## 1.0.3 - 2020-06-03

### Added

- Debugging output for preview field

## 1.0.4 - 2020-06-03

### Changed

- Fixed issue with "Position Fieldtype" plugin

## 1.0.5 - 2020-07-02

### Changed

- Fixed issue with a stray console log

## 1.0.6 - 2020-07-17

### Changed

- Quick-fix for soft-deleted assets issue

## 1.0.7 - 2020-07-17

### Changed

- Fixed with matrix fields and max-blocks. Now the max-block setting will be respected
- Added a "take-over" option which can be disabled so that the preview field augments the matrix field as opposed to taking it over. This allows use alongside Spoon for example

## 1.1.0 - 2020-10-28

### Added

- Ability to enable/disable previews for particular matrix fields (Issue #31)
- Ability to control "takeover" of default Craft UI experience for particular matrix fields
- Block types are now grouped by their matrix field (Issue #30)
  
### Changed

- Improved settings page. Setting should now be available without needing to enable the settings (Issue #18)
- Fixed bug with enabling/disabling "add block" button when max block types reached
- Fixed deprecation for Composer 2.0 (Issue #43)
- Fixed bug with block type names (Issue #32)
- Refactored code base for easier maintenance

## 1.1.1 - 2020-10-28

### Changed

- Release fixes. Paths were incorrect after making changes for composer 2

## 1.2.0 - 2020-11-16

### Added

- Beta Neo support (Issue #17)

### Changed

- Updated interface look and feel
- Added custom icon to preview button
- Overhauled asset bundles
- Fixed issue with Garnish events (Issue #41)
