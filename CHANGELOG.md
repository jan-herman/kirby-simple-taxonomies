# Changelog

## [3.0.0] - 2026-04-02
### Addded
- support for Kirby 5 (new synced structure field)

### Changed
- directory structure (index.php split into multiple files)

### Removed
- deprecated pages methods
    - `$pages->taxonomyTerms()`
- deprecated page methods
    - `$page->taxonomyTerms()`
    - `$page->taxonomyArchiveSlug()`


## [2.5.0] - 2025-12-16
### Added
- `$min_matches` (int|bool) parameter to `filterByTerms` pages method
    - options: true (default) = all terms must match, false = at least one term must match, int = minimum number of matching terms


## [2.4.0] - 2025-07-21
### Changed
- `filterByTerms` pages method now accepts both `Terms` and a single `Term` object as a parameter


## [2.3.0] - 2025-06-10
### Changed
- `field/taxonomy-terms` uses singular title if available


## [2.2.0] - 2025-04-23
### Added
- optional `params parameter` to `Term::url()` method  (useful for combining multiple filters)


## [2.1.1] - 2024-06-12
### Added
- `translate: false` to taxonomy-terms field blueprint


## [2.1.0] - 2024-04-25
### Added
- plugin options: `paramValue`, `paramValueSeparator`, `fieldValueSeparator`
- `$pages->filterByTerms()` method

### Changed
- major refactoring
    - `Structure` replaced with dedicated `Terms` class
- `$pages->taxonomyTerms()` deprecated in favor of `$pages->taconomy()`
- `$page->taxonomyTerms()` deprecated in favor of `$page->taconomy()`
- `$page->taxonomyArchiveSlug()` deprecated in favor of `$page->taxonomyParam()`


## [2.0.0] - 2024-01-19
### Added
- hidden uuid field
- slug validation (ensures the slug is unique)

### Changed
- plugin now saves category uuid instead of slug
- classic structure field was replaced by `synced-structure-field`

### Fixed
- Missing translations


## [1.0.0] - 2023-02-20
### Added
- Initial release
