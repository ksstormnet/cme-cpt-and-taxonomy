# Technical Implementation Details for Persona Content System

This document provides developers with specific code patterns, hooks, and examples for implementing and extending the personas plugin.

## Table of Contents

- [Technical Implementation Details for Persona Content System](#technical-implementation-details-for-persona-content-system)
	- [Table of Contents](#table-of-contents)
	- [System Components](#system-components)
	- [Current Implementation](#current-implementation)
	- [Planned Extensions](#planned-extensions)
	- [Storage Method](#storage-method)
	- [Integration Points](#integration-points)
	- [Code Examples](#code-examples)
		- [Creating and Managing Personas](#creating-and-managing-personas)
		- [Retrieving Persona Data](#retrieving-persona-data)
		- [Extending the System](#extending-the-system)
	- [Performance Considerations](#performance-considerations)
	- [Edge Cases and Error Handling](#edge-cases-and-error-handling)

## System Components

The persona content system consists of the following core components:

1. **Custom Post Types** - Handles registration of the Persona post type
2. **Admin Interface** - UI for managing personas and their properties
3. **Plugin Core** - Main plugin functionality and hooks

## Current Implementation

The current implementation provides:

1. **Persona Management**: Custom post type for creating and managing personas
2. **Gender-Specific Images**: Ability to associate different images with personas based on gender

## Planned Extensions

Future extensions to the system will include:

1. **Redis-Based Caching**: Optional performance enhancement for content delivery
2. **Content Personalization**: Block-level content variations based on active persona
3. **Persona Detection**: Methods for identifying the current visitor's persona
4. **Frontend APIs**: Template functions, hooks, and shortcodes for interacting with personas

## Storage Method

The personas plugin uses WordPress post types and meta data:

1. **Post Type**: 'persona' - Stores basic persona information
2. **Post Meta**: Stores gender-specific images using meta keys:
   - 'persona_image_male'
   - 'persona_image_female'
   - 'persona_image_indeterminate'

## Integration Points

The following integration points are available:

1. **API Functions**:
   - `get_personas()` - Retrieves available personas
   - `get_persona_image($persona_id, $gender)` - Gets gender-specific image for a persona

2. **Hooks**:
   - `personas_register` - Fires after personas are registered
   - `personas_loaded` - Fires when the plugin has fully loaded

## Code Examples

### Creating and Managing Personas

The plugin provides a custom post type for persona management that can be accessed through the WordPress admin interface. Each persona is a custom post with specific meta data:

```php
// Example of creating a persona programmatically
$persona_id = wp_insert_post(array(
    'post_type' => 'persona',
    'post_title' => 'Easy-Breezy Visitor',
    'post_status' => 'publish',
    'post_excerpt' => 'Casual visitors looking for simplicity',
));

// Add gender-specific images
update_post_meta($persona_id, 'persona_image_male', $male_image_id);
update_post_meta($persona_id, 'persona_image_female', $female_image_id);
update_post_meta($persona_id, 'persona_image_indeterminate', $neutral_image_id);
```

### Retrieving Persona Data

To work with personas in your theme or plugin:

```php
// Get all published personas
$personas = get_posts(array(
    'post_type' => 'persona',
    'post_status' => 'publish',
    'numberposts' => -1,
));

// Get a specific persona
$persona = get_post($persona_id);

// Get gender-specific image
$male_image_id = get_post_meta($persona_id, 'persona_image_male', true);
$male_image_url = wp_get_attachment_image_url($male_image_id, 'full');
```

### Extending the System

Custom functionality can be added using WordPress filters and actions:

```php
// Add custom meta boxes to the persona edit screen
add_action('add_meta_boxes', function() {
    add_meta_box(
        'persona_preferences',
        'Persona Preferences',
        'render_preferences_metabox',
        'persona',
        'normal',
        'high'
    );
});

// Save custom meta box data
add_action('save_post_persona', function($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    // Verify nonce and permissions
    
    // Save custom meta
    if (isset($_POST['persona_preference'])) {
        update_post_meta(
            $post_id,
            'persona_preference',
            sanitize_text_field($_POST['persona_preference'])
        );
    }
});
```

## Performance Considerations

1. **Minimize Database Queries**:
   - Cache persona data when possible
   - Use transients for frequently accessed information
   
2. **Future Optimizations**:
   - Redis caching for high-traffic sites
   - Content preloading and background processing

## Edge Cases and Error Handling

1. **Fallback Handling**:
   ```php
   // When retrieving persona-specific content
   function get_persona_image($persona_id, $gender) {
       $image_id = get_post_meta($persona_id, "persona_image_{$gender}", true);
       
       if (empty($image_id)) {
           // Fall back to default/indeterminate image
           $image_id = get_post_meta($persona_id, 'persona_image_indeterminate', true);
       }
       
       if (empty($image_id)) {
           // Fall back to featured image
           $image_id = get_post_thumbnail_id($persona_id);
       }
       
       return $image_id;
   }
   ```

2. **Invalid Persona Handling**:
   ```php
   function is_valid_persona($persona_id) {
       $persona = get_post($persona_id);
       return ($persona && 'persona' === $persona->post_type && 'publish' === $persona->post_status);
   }
   ```
