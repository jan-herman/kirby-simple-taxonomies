# Changelog

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
