# Changelog

All notable changes to the CME Personas plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2025-03-13

### Added

- Frontend integration for persona content display
- New shortcodes for conditional content based on personas
- Persona switcher shortcode for the frontend
- Comprehensive documentation for frontend integration
- Block editor integration for persona content preview

### Changed

- Improved code linting across all languages (PHP, JS, CSS, Markdown)
- Enhanced dashboard UI with better organization
- Updated React components to follow modern patterns
- Refined JavaScript with better error handling

### Fixed

- PHP linting issues in Dashboard and Frontend classes
- JavaScript errors in block editor integration
- CSS formatting and consistency issues
- Markdown linting in documentation files
- Fixed code that was incorrectly scanning node_modules and vendor directories

## [1.2.0] - 2025-03-13

### Added

- Cache management system with WordPress cache API integration
- Implemented in-memory caching for database queries
- Added cache invalidation when content is updated
- Proper security measures for user input sanitization

### Changed

- Improved error handling in content retrieval methods
- Enhanced documentation with better inline comments
- Reorganized code structure for better maintainability
- Updated all function docblocks to comply with WordPress coding standards

### Fixed

- PHP linting issues related to docblocks and function return types
- Improved data sanitization and validation throughout
- Fixed markdown formatting issues in documentation
- Added proper file headers to all PHP files

## [1.1.0] - 2025-02-15

### Added

- Initial implementation of persona content storage
- Core API for retrieving persona-specific content
- Admin UI for managing persona content variations
- Basic integration with WordPress content system

### Changed

- Refactored database access for better performance
- Enhanced API with better error handling
- Improved documentation

## [1.0.0] - 2025-01-20

### Added

- Initial release
- Persona management functionality
- Basic admin interface
- Integration with WordPress user system
