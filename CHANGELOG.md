# Matrix Field Preview Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [5.4] - 2026-01-28

### Added

- Inline lightswitch toggles in settings tables for quickly enabling/disabling field previews and block type previews without navigating to individual edit pages

## [5.3.5] - 2025-09-16

### Fixed

- Fix for migration issue #130

## [5.3.4] - 2025-09-16

### Added

- Previews now handle matrix fields with overridden handles. See issue #137.

## [5.3.3] - 2025-09-16

### Added

- Ability to set default values for new fields and previews. See issues #143.

## [5.3.2] - 2025-08-04

### Fixed

- Fixed issuew with the schemaVersion of the plugin which may have prevented migrations from running.

## [5.3.1] - 2025-06-26

### Fixed

- Fixed issue with field config query not using the correct table prefix for fields.dateDeleted, which could cause errors on installs with a custom table prefix.

## [5.3.0] - 2025-06-19

### Added

- New inline action menu item for content previews

## [5.2.1] - 2025-06-18

### Fixed

- Added minimum version of Craft 5.6 for recent release (support for read-only settings page functionality)

## [5.2.0] - 2025-06-17

### Bug

- This was released without a constraint on the compatible Craft version. Craft 5.6 is required (support for read-only settings page functionality)

### Fixed

- The icon for the plugin wasn't appearing in the settings page when "allow admin changes" was false, making it unclear that you can always edit your previews in production. This has been fixed.
- The breadcrumbs in the settings page were malformed and have now been fixed to make the settings page experience more consistant with other plugins.

## [5.1.0] - 2025-06-11

### Added

- New "button label" field that lets you customise the text of the preview button on a per-field basis. Previously this was only possible at a "global" level via translations (see issue #135).
- New button icons that let you customise the look of the preview button.
- The ability to disable particular previews so that they don't show in the modal.

### Changed

- Added the ability to edit each field configuration via a separate settings page, allowing for future per-field settings.
- Preview modal sidebar will only show when a block has been assigned to a category.
- Preview modal sidebar only shows categories now that have a block assigned to them.
- Changed nested neo blocks to insert new blocks at the end rather than the start of the parent block.

### Fixed

- Fixed a bug with the preview button not properly "taking over" the default button.
- Fixed layout issue with button when pasting a matrix field (issue #136).

## 5.0.1 - 2025-01-30

### Fixed

- [Add fix for Neo field takeover button styling. Issue #129](https://github.com/timmyomahony/craft-matrix-field-preview/issues/129)

## 5.0.0 - 2024-08-08

**🚨 Read Before Updating**

We need to reset the Matrix Field Preview data as part of the Craft 5 upgrade. Unfortunately there's no way to migrate existing data from version 4 to 5, due to the changes in matrix fields in Craft 5 (specifically the migration from "block types" to "entry types").

Therefore, after updating to version 5 you'll need to re-enter titles, descriptions and images for the entry types within your matrix fields. Neo fields are unaffected, as is any other data on your site.

**⚠️ Matrix Field "Views"**

Currently, only the "block" view for matrix fields is supported. Support for the "card" and "element index" views will follow shortly in a future update (5.1). This is due to requiring some additions in the Craft codebase to be able to detect these new views in the Craft CP Javascript environment.

### Added

- Craft 5 support

### Fixed

- Fixed "add block above" issue with Neo fields (see Issue #112 and PR #123)
- Stop page scroll bug when modal is closed (Issue #106)

## 4.1.3 - 2024-03-01

### Fixed

- Neo previews now respect the "enabled" tag. (Issue #118 & PR #121)

## 4.1.2 - 2023-12-06

### Added

- Support for GIFs when transforms are disabled (issue #119). See the [troubleshooting](https://craft-plugins.timmyomahony.com/matrix-field-preview/docs/troubleshooting) docs for more on issues with GIFs.

## 4.1.1 - 2023-10-26

### Changed

- Updated branding! Shiny new icon that better suits the Craft CMS aesthetic, as well as [a new documentation website](https://craft-plugins.timmyomahony.com/matrix-field-preview).

## 4.1.0 - 2023-10-04

### Added

- Added basic MatrixMate compatibility (issue #109 & PR #90).
- Added proper static translation, allowing interface to be updated (issue #110).
- Ability to disable previews on nested Neo fields, if there's only a single child (issue #111).

## 4.0.6 - 2023-09-6

### Fixed

- Fixed inline previews not showing on the top-level Neo blocks (issue #103).
- Fixed issue with "add block above" option on Neo menus not working (issue #104).
- Fixed issue with Spoon compatibility (issue #100).

### Changed

- Changed preview image transform from "stretch" to "fit", possibly addressing issue #105.

## 4.0.5 - 2022-09-21

Thanks @ttempleton for PR #91, helping to address issues with Neo.

### Added

- Ability to takeover the default Neo block button (addressing issue #85 and potentially #84)

### Fixed

- Fixed inline previews not showing in Neo fields (issue #92)
- Added/fixed proper modal filtering for Neo fields, now the modal respects the children available to the block (issue #87)
- Fixed bug with Neo block configuration crashing the previews (issue #88)
- Fixed Supertable "matrix row" issue, where previews were incorrectly showing in nested Supertable rows for Matrix Fields (issue #96)

## 4.0.4 - 2022-09-06

### Fixed

- Fixed issue with `sortOrder` missing (issue #95)

## 4.0.3 - 2022-08-24

### Fixed

- Fixed issue with Neo migration not being run correctly (issue #88)
- Fixed issue with ordering > 10 fields (issue #93)

## 4.0.2 - 2022-06-15

### Fixed

- Version 4 of the plugin still included Craft 3/PHP 7 as valid requirements so these have been removed.

## 4.0.1 - 2022-06-14

### Fixed

- Issue with preview image upload (Issue #82)

## 4.0.0 - 2022-06-13

### Added

- Craft 4 support!
- Added phpstan and rector for code quality.
- Updated types for Craft 4 and PHP 8 support.

### Changed

- Split JavaScript asset bundles out for clarity and readability.
- Improved the JavaScript for consistant loading.

### Fixed

- Issue with Neo field configuration settings page
- Small display issue with inline previews.

## 3.0.2 - 2022-06-10

### Fixed

- Improved migrations to address #77.
- Added missing "Install" migrations.

## 3.0.1 - 2022-06-09

### Fixed

- Updated the admin permissions being used to address #75.

## 3.0.0 - 2022-06-07

After an unplanned hiatus we're back with a whole load of useful updates.

Note that we've jumped from version 1.X.X to 3.X.X. This is to make it easier and clearer to add Craft 4 support: plugin version 3.X.X (this version) will continue to work for Craft 3 while plugin version 4.X.X will work for Craft 4 (eventually).

### Added

- Categories. Blocks can be assigned to categories for easier filtering via the new categories sidebar.
- Search. You can now search via the modal preview, making it much quick to find and select matrix field blocks.
- Sorting. You can now set the order of previews via a simple drag-and-drop interface. This means you can put the most common blocks near the top.
- Full-screen preview. You can now view a full-screen preview of the ... preview.
- Markdown descriptions. Added ability to add markdown to descriptions.

### Changed

- New modal design. Completely overhauled the modal overlay for easier search and filtering.
- Improved the settings templates with breadcrumbs etc.
- Code cleanup. Overhauled the codebase for easier maintainance and prep for Craft 4.

### Fixed

- Fixed issue #62 with Supertable via @daltonrooney PR.

## 1.2.3 - 2020-11-23

### Changed

- Fixed migration issues with latest release.

## 1.2.0 - 2020-11-16

### Added

- Beta Neo support (Issue #17)

### Changed

- Updated interface look and feel.
- Added custom icon to preview button.
- Overhauled asset bundles.
- Fixed issue with Garnish events (Issue #41).

## 1.1.1 - 2020-10-28

### Changed

- Release fixes. Paths were incorrect after making changes for composer 2.

## 1.1.0 - 2020-10-28

### Added

- Ability to enable/disable previews for particular matrix fields (Issue #31).
- Ability to control "takeover" of default Craft UI experience for particular matrix fields.
- Block types are now grouped by their matrix field (Issue #30).

### Changed

- Improved settings page. Setting should now be available without needing to enable the settings (Issue #18).
- Fixed bug with enabling/disabling "add block" button when max block types reached (Issue #71)
- Fixed deprecation for Composer 2.0 (Issue #43).
- Fixed bug with block type names (Issue #32).
- Refactored code base for easier maintenance.

## 1.0.7 - 2020-07-17

### Changed

- Fixed with matrix fields and max-blocks. Now the max-block setting will be respected.
- Added a "take-over" option which can be disabled so that the preview field augments the matrix field as opposed to taking it over. This allows use alongside Spoon for example.

## 1.0.6 - 2020-07-17

### Changed

- Quick-fix for soft-deleted assets issue.

## 1.0.5 - 2020-07-02

### Changed

- Fixed issue with a stray console log.

## 1.0.4 - 2020-06-03

### Changed

- Fixed issue with "Position Fieldtype" plugin.

## 1.0.3 - 2020-06-03

### Added

- Debugging output for preview field.

## 1.0.2 - 2020-06-03

### Changed

- Fixed issues with default preview images.

## 1.0.1 - 2020-05-23

### Changed

- Fixed issue with the JavaScript asset bundle running on non-cp requests.

## 1.0.0 - 2020-04-01

### Added

- Initial release.
