# Cruise Made Easy - Personas

A WordPress plugin that manages customer personas for personalized content delivery in cruise websites, enabling targeted experiences based on visitor preferences and travel styles.

## Description

Cruise Made Easy Personas is at the core of the website's personalized user experience system. It provides functionality for maintaining customer personas/niches that power the content personalization system. The plugin enables different content presentation based on visitor-selected personas, creating a tailored browsing experience for each user.

### Features

- Creates a Customer Persona custom post type
- Provides a structured way to organize customer information
- Fully compatible with the WordPress block editor
- Personalized content delivery based on user persona
- Cache management system for improved performance
- Secure handling of user preferences

## Requirements

- WordPress 6.7.2 or higher
- PHP 8.3 or higher

## Installation

1. Upload the `cme-personas` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Start using the Customer Persona post type

## Usage

### Customer Personas

To create a new customer persona:

1. Go to "Customer Personas" in the admin menu
2. Click "Add New"
3. Add title, excerpt, and featured image
4. Publish your persona

## Frequently Asked Questions

### Does this plugin work with Gutenberg?

Yes, the Customer Persona post type is fully compatible with the WordPress block editor (Gutenberg).

### Can I customize the fields for Customer Personas?

The plugin provides a standard WordPress post editing experience. You can use custom fields or extend the functionality with custom blocks.

## Development

This plugin follows WordPress coding standards and uses modern PHP 8.3 features.

### Requirements for Development

- PHP 8.3+
- Composer (for development dependencies)

## Changelog

### 1.2.0 (March 13, 2025)

- Added cache management system with WordPress cache API integration
- Implemented in-memory caching for database queries
- Added cache invalidation when content is updated
- Improved error handling in content retrieval methods
- Fixed PHP linting issues and improved code documentation
- Enhanced security with proper input sanitization

### 1.1.0 (February 15, 2025)

- Added persona content personalization system
- Core API for retrieving persona-specific content
- Admin UI for managing persona content variations
- Updated minimum PHP version requirement to 8.3
- Updated minimum WordPress version requirement to 6.7.2

### 1.0.0 (January 20, 2025)

- Initial release with persona management functionality

## License

This plugin is proprietary software owned by Sky+Sea LLC d/b/a KSstorm Media. All rights reserved.
