# Technical Implementation Details for Persona Content System

This document provides developers with specific code patterns, hooks, and examples for implementing persona-specific content using the standalone Personas plugin.

## Table of Contents

- [Technical Implementation Details for Persona Content System](#technical-implementation-details-for-persona-content-system)
	- [Table of Contents](#table-of-contents)
	- [System Components](#system-components)
	- [Integration Points](#integration-points)
	- [Code Examples](#code-examples)
		- [Detecting Current Persona](#detecting-current-persona)
		- [Retrieving Persona-Specific Content](#retrieving-persona-specific-content)
		- [Creating Persona-Specific Content](#creating-persona-specific-content)
		- [Cache Management](#cache-management)
	- [Storage Method](#storage-method)
	- [Performance Considerations](#performance-considerations)
	- [Edge Cases and Error Handling](#edge-cases-and-error-handling)
	- [Integration with Cruise Made Easy](#integration-with-cruise-made-easy)

## System Components

The persona content system consists of the following core components:

1. **Persona Manager** - Handles persona detection, verification, and context
2. **Content Storage** - Stores and retrieves persona-specific content using post meta
3. **Admin Interface** - UI for managing persona content variations
4. **Block Parser** - Identifies and processes blocks for persona-specific variations
5. **Optional Caching** - Redis-based caching layer for improved performance

These components work together to provide a seamless persona-based content experience.

## Integration Points

The following WordPress hooks and filters are used to integrate persona content:

1. **Content Filters**:
   - `the_content` - Filter post content with persona-specific variations
   - `the_title` - Filter post titles when appropriate
   - `get_the_excerpt` - Filter excerpts for persona-specific content
   - `post_thumbnail_id` - Filter featured images for personas

2. **Template Functions**:
   - `personas_get_current_persona()` - Get the current persona identifier
   - `personas_get_content($post_id)` - Get persona-specific content for current visitor
   - `personas_is_valid_persona($persona)` - Verify if a persona identifier is valid

3. **Shortcodes**:
   - `[persona_content persona="easy-breezy"]Content here[/persona_content]` - Display content for specific persona
   - `[persona_image persona="easy-breezy" default="default-image.jpg"]easy-breezy-image.jpg[/persona_image]` - Display persona-specific images

4. **JavaScript API**:
   - `Personas.getCurrent()` - Get current persona
   - `Personas.setPersona(persona)` - Set persona for current session
   - `Personas.refreshContent()` - Refresh content for current persona

## Code Examples

### Detecting Current Persona

```php
// In template files
function personas_get_current_persona() {
    // Access the singleton instance
    $persona_manager = Personas_Manager::get_instance();
    return $persona_manager->get_current_persona();
}

// Usage example
$current_persona = personas_get_current_persona();
if ($current_persona === 'easy-breezy') {
    // Show simplified content
} else if ($current_persona === 'luxe') {
    // Show luxury content
}
```

### Retrieving Persona-Specific Content

```php
// Retrieve content with automatic persona detection
function personas_get_content($post_id, $field = 'post_content') {
    $content_handler = Personas_Content::get_instance();
    return $content_handler->get_persona_content($post_id, $field);
}

// Retrieve content for a specific persona
function personas_get_content_for_persona($post_id, $persona, $field = 'post_content') {
    $content_handler = Personas_Content::get_instance();

    // Get variations
    $variations = $content_handler->get_variations($post_id, $persona);

    // Return specific field if it exists
    if (isset($variations[$field])) {
        return $variations[$field];
    }

    // Get default content if no variation exists
    $post = get_post($post_id);
    if ($field === 'post_content') {
        return $post->post_content;
    } else if ($field === 'post_title') {
        return $post->post_title;
    }

    // For other fields, try post meta
    return get_post_meta($post_id, $field, true);
}

// Usage example
echo personas_get_content(get_the_ID());

// Compare content across personas
$default_content = personas_get_content_for_persona(get_the_ID(), 'default');
$easy_content = personas_get_content_for_persona(get_the_ID(), 'easy-breezy');
```

### Creating Persona-Specific Content

```php
// Save persona-specific content
function personas_save_content($post_id, $persona, $variations) {
    $content_handler = Personas_Content::get_instance();
    return $content_handler->save_variations($post_id, $persona, $variations);
}

// Usage in admin
add_action('save_post', function($post_id, $post, $update) {
    // Skip if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Skip if this is not the right post type or no persona data was submitted
    if (!isset($_POST['personas']) || !is_array($_POST['personas'])) {
        return;
    }

    // Security check
    if (!isset($_POST['personas_meta_nonce']) ||
        !wp_verify_nonce($_POST['personas_meta_nonce'], 'personas_save_meta')) {
        return;
    }

    // Save content for each persona from form fields
    foreach ($_POST['personas'] as $persona => $data) {
        // Prepare variations array
        $variations = array();

        // Add title if provided
        if (!empty($data['title'])) {
            $variations['title'] = sanitize_text_field($data['title']);
        }

        // Add featured image if provided
        if (!empty($data['featured_image'])) {
            $variations['featured_image'] = absint($data['featured_image']);
        }

        // Add block content variations if provided
        if (!empty($data['blocks']) && is_array($data['blocks'])) {
            foreach ($data['blocks'] as $block_id => $content) {
                // Skip empty content
                if (empty($content)) {
                    continue;
                }

                // Sanitize and add to variations
                $variations[$block_id] = wp_kses_post($content);
            }
        }

        // Save variations for this persona
        personas_save_content($post_id, $persona, $variations);
    }
}, 10, 3);
```

### Cache Management

```php
// Optional Redis caching layer
function personas_enable_caching($enabled = true) {
    $option_name = 'personas_redis_caching';
    return update_option($option_name, $enabled);
}

// Clear cache for a specific post
function personas_clear_cache($post_id) {
    // Check if caching is enabled
    if (!get_option('personas_redis_caching', false)) {
        return true;
    }

    $cache_manager = Personas_Cache::get_instance();
    $personas = personas_get_available_personas();

    foreach ($personas as $persona_id => $persona_name) {
        $cache_key = "personas:{$persona_id}:post:{$post_id}";
        $cache_manager->delete($cache_key);
    }

    return true;
}

// Example of using the cache layer with fallback
function personas_get_cached_content($post_id, $persona) {
    // Check if caching is enabled
    if (!get_option('personas_redis_caching', false)) {
        return personas_get_content_for_persona($post_id, $persona);
    }

    $cache_manager = Personas_Cache::get_instance();
    $cache_key = "personas:{$persona}:post:{$post_id}";

    // Try to get from cache
    $cached_content = $cache_manager->get($cache_key);

    if (false !== $cached_content) {
        return $cached_content;
    }

    // Generate content
    $content = personas_get_content_for_persona($post_id, $persona);

    // Cache the content
    $cache_manager->set($cache_key, $content, WEEK_IN_SECONDS);

    return $content;
}
```

## Storage Method

The personas plugin uses WordPress post meta for storage, with a structured approach:

```php
// Meta key format: _personas_{persona_id}_variations
// Meta value: Serialized array of content variations

// Example structure of the variations array:
$variations = array(
    'title' => 'Custom title for easy-breezy persona',
    'featured_image' => 123, // Attachment ID
    'block_1_id' => 'Custom content for block 1',
    'block_2_id' => 'Custom content for block 2',
    // Other variations...
);

// Storing in post meta
update_post_meta($post_id, "_personas_{$persona}_variations", $variations);

// Retrieving from post meta
$variations = get_post_meta($post_id, "_personas_{$persona}_variations", true);
```

Using post meta offers several advantages:

1. Works with WordPress's built-in data management
2. Automatically handles post revisions and deletions
3. Requires no custom database tables
4. Compatible with existing backup and migration tools

## Performance Considerations

When implementing persona content, keep the following performance guidelines in mind:

1. **Minimize Database Queries**:
   - Group related queries when possible
   - Avoid nested loops of database operations
   - Consider enabling the Redis caching layer for high-traffic sites

2. **Optimize Content Storage**:
   - Store only what differs from the default content
   - Use block IDs for targeted content changes
   - Structure variations for efficient lookups

3. **Front-end Performance**:
   - Parse blocks only once per page load
   - Cache persona content when appropriate
   - Consider using transients for frequently accessed content

4. **Admin Performance**:
   - Load block editor enhancements only when needed
   - Use batch processing for operations across many posts
   - Implement progressive loading for large content sets

## Edge Cases and Error Handling

1. **Fallback Content**:
   ```php
   $persona_content = personas_get_content_for_persona($post_id, 'easy-breezy');

   if (empty($persona_content)) {
       // Fall back to default persona
       $persona_content = personas_get_content_for_persona($post_id, 'default');
   }

   return $persona_content;
   ```

2. **Invalid Persona Handling**:
   ```php
   $requested_persona = $_GET['persona'] ?? '';

   // Get the persona manager
   $persona_manager = Personas_Manager::get_instance();

   if (!$persona_manager->is_valid_persona($requested_persona)) {
       $requested_persona = 'default';
   }
   ```

3. **Redis Connectivity Issues**:
   ```php
   function personas_safely_get_cached_content($post_id, $persona) {
       try {
           // Try to get cached content
           return personas_get_cached_content($post_id, $persona);
       } catch (Exception $e) {
           // Log the error
           error_log('Redis cache error: ' . $e->getMessage());

           // Fall back to direct content retrieval
           return personas_get_content_for_persona($post_id, $persona);
       }
   }
   ```

4. **Malformed Block Content**:
   ```php
   function personas_safely_parse_blocks($content) {
       try {
           $blocks = parse_blocks($content);
           return $blocks;
       } catch (Exception $e) {
           error_log('Block parsing error: ' . $e->getMessage());
           return array();
       }
   }
   ```

## Integration with Cruise Made Easy

To integrate the standalone Personas plugin with Cruise Made Easy:

1. **Plugin Dependency**:
   ```php
   // In cme-cruises.php
   function cme_cruises_check_dependencies() {
       if (!is_plugin_active('personas/personas.php')) {
           add_action('admin_notices', 'cme_cruises_personas_missing_notice');
       }
   }
   add_action('admin_init', 'cme_cruises_check_dependencies');

   function cme_cruises_personas_missing_notice() {
       ?>
       <div class="notice notice-error">
           <p><?php _e('Cruise Made Easy requires the Personas plugin to be installed and activated.', 'cme-cruises'); ?></p>
       </div>
       <?php
   }
   ```

2. **Post Type Integration**:
   ```php
   // Register Cruise Made Easy post types with Personas
   function cme_cruises_register_persona_post_types($post_types) {
       $post_types[] = 'cme_cruise';
       $post_types[] = 'cme_ship';
       $post_types[] = 'cme_destination';
       return $post_types;
   }
   add_filter('personas_supported_post_types', 'cme_cruises_register_persona_post_types');
   ```

3. **Persona Configuration**:
   ```php
   // Register cruise-specific personas
   function cme_cruises_register_personas($personas) {
       // These could already be defined in the plugin, but ensure they exist
       $personas['easy-breezy'] = __('Easy-Breezy Cruiser', 'cme-cruises');
       $personas['luxe'] = __('Luxe Seafarer', 'cme-cruises');
       $personas['thrill-seeker'] = __('Thrill Seeker', 'cme-cruises');
       return $personas;
   }
   add_filter('personas_available_personas', 'cme_cruises_register_personas');
   ```

4. **Template Integration**:
   ```php
   // In template files
   function cme_get_cruise_description($cruise_id) {
       if (function_exists('personas_get_content')) {
           return personas_get_content($cruise_id, 'description');
       }

       // Fallback if Personas plugin isn't active
       return get_post_meta($cruise_id, '_cruise_description', true);
   }
