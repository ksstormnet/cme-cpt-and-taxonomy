=== Cruise Made Easy - Personas ===
Contributors: ksstorm
Tags: customer personas, content personalization, cruise, user experience
Requires at least: 6.7.2
Tested up to: 6.7.2
Stable tag: 1.2.0
Requires PHP: 8.3
License: Proprietary
License URI: All rights reserved

Manages customer personas for personalized content delivery in cruise websites, enabling targeted experiences based on visitor preferences and travel styles.

== Description ==

Cruise Made Easy Personas is central to the website's personalized user experience. It provides essential functionality for maintaining customer personas/niches that power the content personalization system. The plugin enables different content presentation based on visitor-selected personas, creating a customized experience for each user based on their preferences and travel style.

Features:
* Creates a Customer Persona custom post type
* Powers the content personalization system
* Enables targeted content delivery based on visitor preferences
* Creates a personalized user journey through the website
* Efficient caching for improved performance
* Secure handling of user preferences and data

== Installation ==

1. Upload the `cme-personas` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Start using the Customer Persona post type to manage your customer personas

== Frequently Asked Questions ==

= How do personas improve the user experience? =

Personas allow visitors to identify their travel preferences and style, which the website uses to display personalized content tailored to their interests.

= How does content personalization work? =

When a visitor selects a persona, the website dynamically adjusts content presentation to match their preferences, creating a more relevant browsing experience.

= Does this plugin work with Gutenberg? =

Yes, the Customer Persona post type is fully compatible with the WordPress block editor (Gutenberg).

= How does this plugin integrate with other Cruise Made Easy plugins? =

This plugin forms the foundation of the personalization system and works with other CME plugins to deliver a cohesive, personalized user experience throughout the site.

== Screenshots ==

1. Customer Persona editing screen
2. Persona selection interface for visitors
3. Personalized content example

== Changelog ==

= 1.2.0 =
* Added cache management system with WordPress cache API integration
* Implemented in-memory caching for database queries
* Added cache invalidation when content is updated
* Improved error handling in content retrieval methods
* Fixed PHP linting issues and improved code documentation
* Enhanced security with proper input sanitization

= 1.1.0 =
* Added persona content personalization system
* Core API for retrieving persona-specific content
* Admin UI for managing persona content variations
* Updated minimum PHP version requirement to 8.3
* Updated minimum WordPress version requirement to 6.7.2

= 1.0.0 =
* Initial release with persona management functionality

== Upgrade Notice ==

= 1.2.0 =
Adds caching system for improved performance, better security, and code quality improvements.

= 1.1.0 =
Updates minimum PHP and WordPress requirements. Improves plugin description and documentation.
