# Changelog

All notable changes to the CME Personas plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- Refactored plugin architecture to follow Single Responsibility Principle:
  - Split `class-persona-manager.php` into specialized components:
    - `class-personas-detector.php`: Handles persona detection from various sources
    - `class-personas-storage.php`: Manages storage of persona preferences
    - `class-personas-repository.php`: Retrieves persona data from database
  - Renamed `class-plugin.php` to `class-personas-loader.php` for clarity
  - Created `class-personas-facade.php` as a simplified API layer
  - Added `class-personas-assets.php` for script/style management
  - Added backward compatibility layer to maintain API stability

## [1.5.1] - 2025-03-14

### Fixed

- Fixed fatal error caused by missing class-persona-content.php file

### Added

- Improved development documentation with clear guidelines on repository hygiene
- Added comprehensive semantic versioning guidelines
- Added documentation on questioning unclear instructions

### Changed

- Enhanced development workflow documentation with continuous documentation update requirements
- Improved AI assistant guidelines with best practices
- Updated architecture documentation version references

## [1.5.0] - 2025-03-13

## [1.4.2] - 2025-03-13

### Fixed

- Fixed compatibility issue with Meta Slider shortcodes
- Resolved duplicate shortcode registration between Frontend and API classes
- Improved shortcode processing to avoid conflicts with third-party plugins

## [1.4.1] - 2025-03-13

### Fixed

- Fixed "Class 'CME_Personas\Persona_Manager' not found" error by adding proper class dependencies
- Fixed PHP 8 compatibility issues with parameter order in set_content methods
- Fixed "headers already sent" warnings caused by deprecation notices

## [1.4.0] - 2025-03-13

### Added

- Specialized Meta Slider compatibility handling
- Boundary-based shortcode approach for persona content
- CSS fixes for Meta Slider integration with persona content

### Changed

- Improved shortcode processing to use boundary-based approach
- Refactored frontend class for better third-party compatibility
- Simplified shortcode handling to prevent interference with other plugins
- Updated plugin display name to "Cruise Made Easy - Personas" for better branding

### Fixed

- Fixed compatibility issue with Meta Slider shortcodes
- Resolved duplicate shortcode registration between Frontend and Shortcodes classes
- Improved shortcode processing to avoid conflicts with third-party plugins
- Fixed React Hook dependency warnings in block editor component

## [1.4.1] - 2025-03-01

### Added

- Integration with Elementor for persona-specific elements
- Support for persona detection in AMP pages
- New action hooks for third-party integrations

### Changed

- Improved performance of persona detection algorithms
- Enhanced caching of persona-specific content
- Updated compatibility with WordPress 6.4

### Fixed

- Fixed issue with persona detection for mobile users
- Resolved conflict with WooCommerce product pages
- Fixed CSS issues in dark mode admin interface

## [1.4.0] - 2025-02-10

### Added

- Block editor integration for persona content
- REST API endpoints for persona management
- Frontend dashboard for persona analytics
- Comprehensive developer documentation

### Changed

- Refactored codebase to use modern PHP practices
- Improved UI for persona management
- Enhanced performance of persona detection
- Updated JavaScript dependencies

### Fixed

- Resolved issue with media attachment handling
- Fixed compatibility issues with popular caching plugins
- Addressed multiple edge cases in persona detection
