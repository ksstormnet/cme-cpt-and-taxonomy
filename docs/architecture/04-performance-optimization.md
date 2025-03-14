# Persona System Performance Optimization

## Version Information

Current as of v1.5.0

This document outlines performance optimization strategies for the persona content system, with special focus on Redis caching implementation, edge caching, and other performance enhancements.

## Table of Contents

- [Persona System Performance Optimization](#persona-system-performance-optimization)
	- [Version Information](#version-information)
	- [Table of Contents](#table-of-contents)
	- [Performance Overview](#performance-overview)
	- [Redis Object Caching](#redis-object-caching)
		- [Caching with Shortcodes](#caching-with-shortcodes)
		- [Cache Implementation](#cache-implementation)
		- [Redis Configuration](#redis-configuration)
		- [Conditional Caching](#conditional-caching)
	- [Edge Caching Integration](#edge-caching-integration)
		- [Cloudflare Integration](#cloudflare-integration)
		- [Cache Headers](#cache-headers)
		- [Persona-Specific Cache Keys](#persona-specific-cache-keys)
	- [Shortcode Processing Optimization](#shortcode-processing-optimization)
		- [Selective Processing](#selective-processing)
		- [Parse Optimization](#parse-optimization)
	- [Query Optimization](#query-optimization)
		- [Persona Lookups](#persona-lookups)
		- [Content Retrieval](#content-retrieval)
	- [Asset Loading](#asset-loading)
		- [Conditional Enqueuing](#conditional-enqueuing)
		- [Critical CSS](#critical-css)
	- [Implementation Phases](#implementation-phases)
		- [Phase 1: Core Optimization](#phase-1-core-optimization)
		- [Phase 2: Redis Integration](#phase-2-redis-integration)
		- [Phase 3: Edge Caching](#phase-3-edge-caching)
		- [Phase 4: Advanced Optimizations](#phase-4-advanced-optimizations)
	- [Monitoring and Profiling](#monitoring-and-profiling)
		- [Performance Metrics](#performance-metrics)
		- [Debug Logging](#debug-logging)
	- [Related Documentation](#related-documentation)

## Performance Overview

The persona content system is designed for optimal performance through multiple strategies:

1. **Efficient Shortcode Processing** - Only process content that contains persona shortcodes
2. **Redis Object Caching** - Cache processed shortcode output to reduce processing time
3. **Edge Caching Integration** - Cache per-persona pages at the CDN level
4. **Optimized Database Queries** - Minimize and optimize database operations
5. **Conditional Asset Loading** - Only load assets when persona features are in use

## Redis Object Caching

### Caching with Shortcodes

Redis object caching works as a complementary technology to the boundary-based shortcode approach. The shortcode system defines how persona-specific content is structured, while Redis caching optimizes the processing and delivery of that content.

**Key Benefits of Combining Redis with Shortcodes**:

1. Cache the processed shortcode output for each persona
2. Reduce repeated shortcode processing overhead
3. Minimize database queries for persona detection
4. Preserve WordPress compatibility through standard shortcode API
5. Maintain edge caching compatibility

### Cache Implementation

The implementation uses WordPress's Object Cache API with Redis as the backend:

```php
/**
 * Cache processed shortcode content.
 *
 * @param string $content     The content to process and cache.
 * @param string $persona     The current persona.
 * @param int    $post_id     The post ID.
 * @return string             The processed content.
 */
public function maybe_cache_shortcode_output($content, $persona, $post_id) {
    // Skip caching for admin users or when debugging
    if (is_admin() || defined('WP_DEBUG') && WP_DEBUG) {
        return $this->process_persona_shortcodes($content);
    }

    // Check if content has persona shortcodes
    if (strpos($content, '[if_persona') === false) {
        // No persona shortcodes, return content as is
        return $content;
    }

    // Generate cache key
    $cache_key = 'persona_content_' . $persona . '_' . $post_id;

    // Try to get cached content
    $cached_content = wp_cache_get($cache_key, 'persona-content');

    if (false !== $cached_content) {
        // Return cached content
        return $cached_content;
    }

    // Process shortcodes
    $processed_content = $this->process_persona_shortcodes($content);

    // Cache the processed content
    wp_cache_set(
        $cache_key,
        $processed_content,
        'persona-content',
        HOUR_IN_SECONDS
    );

    return $processed_content;
}
```

### Redis Configuration

To enable Redis with WordPress:

1. **Install the Redis Object Cache Plugin**:
   - Install and activate a Redis plugin like "Redis Object Cache"
   - Configure connection settings

2. **Adjust wp-config.php**:

   ```php
   // Enable object caching
   define('WP_CACHE', true);
   
   // Optional: Redis-specific settings
   define('WP_REDIS_HOST', '127.0.0.1');
   define('WP_REDIS_PORT', 6379);
   ```

3. **Optimize Redis Configuration**:
   - Adjust Redis memory allocation based on site needs
   - Set appropriate key eviction policies
   - Configure persistence settings for reliability

### Conditional Caching

To optimize cache usage, cache only what provides performance benefits:

```php
/**
 * Determine if content should be cached.
 *
 * @param string $content  The content to check.
 * @return bool            Whether the content should be cached.
 */
private function should_cache_content($content) {
    // Don't cache small content with no shortcodes
    if (strlen($content) < 1000 && strpos($content, '[if_persona') === false) {
        return false;
    }
    
    // Don't cache certain dynamic content
    if (strpos($content, '[some_dynamic_shortcode') !== false) {
        return false;
    }
    
    return true;
}
```

## Edge Caching Integration

### Cloudflare Integration

The persona system integrates with Cloudflare or similar edge caching solutions:

```php
/**
 * Set cache headers for persona content.
 */
public function set_persona_cache_headers() {
    // Get current persona
    $persona = $this->get_current_persona();
    
    // Set cache headers
    header('Cache-Control: public, max-age=3600');
    header('Vary: Cookie');
    
    // Set Cloudflare-specific cache headers
    header('Cache-Tag: persona-' . $persona);
}
```

### Cache Headers

Setting proper cache headers allows edge caching to work correctly with personas:

```php
/**
 * Set cache headers for edge caching.
 */
function cme_set_edge_cache_headers() {
    $persona_manager = CME_Persona_Manager::get_instance();
    $current_persona = $persona_manager->get_current_persona();
    
    // Set Edge Cache TTL appropriate for content update frequency
    header('Cache-Control: public, max-age=3600, s-maxage=3600');
    
    // Vary by persona cookie
    header('Vary: Cookie');
    
    // Set Cloudflare-specific headers
    header('Cache-Tag: persona-' . esc_attr($current_persona));
}
add_action('send_headers', 'cme_set_edge_cache_headers');
```

### Persona-Specific Cache Keys

Configure edge caching to use persona-specific cache keys:

**Cloudflare Page Rule Example**:

```
URL: *cruisemadeeasy.com/*
Settings:
  - Cache Level: Everything
  - Edge Cache TTL: 2 hours
  - Cache Key: Include Cookie: _cme_persona
```

## Shortcode Processing Optimization

### Selective Processing

Only process content that contains persona shortcodes:

```php
/**
 * Selectively process shortcodes.
 *
 * @param string $content  The content to process.
 * @return string          The processed content.
 */
function cme_maybe_process_shortcodes($content) {
    // Skip if no persona shortcodes are present
    if (strpos($content, '[if_persona') === false) {
        return $content;
    }
    
    // Process persona shortcodes
    return do_shortcode($content);
}
```

### Parse Optimization

Optimize shortcode parsing for large content:

```php
/**
 * Optimize shortcode parsing.
 *
 * @param string $content  The content to parse.
 * @return string          The parsed content.
 */
function cme_optimize_shortcode_parsing($content) {
    // Check for large content
    if (strlen($content) > 50000) {
        // Break content into chunks for better memory usage
        $chunks = str_split($content, 10000);
        $processed = '';
        
        foreach ($chunks as $chunk) {
            $processed .= $this->process_persona_shortcodes($chunk);
        }
        
        return $processed;
    }
    
    // Process normally for smaller content
    return $this->process_persona_shortcodes($content);
}
```

## Query Optimization

### Persona Lookups

Optimize persona detection queries:

```php
/**
 * Optimized persona lookup.
 *
 * @return string  The current persona.
 */
public function get_optimized_persona() {
    static $cached_persona = null;
    
    // Return cached result if available
    if ($cached_persona !== null) {
        return $cached_persona;
    }
    
    // Check for cached persona in object cache
    $cached_persona = wp_cache_get('current_persona_' . $this->get_user_identifier(), 'personas');
    if (false !== $cached_persona) {
        return $cached_persona;
    }
    
    // Perform normal detection
    $persona = $this->detect_persona();
    
    // Cache for this request and in object cache
    $cached_persona = $persona;
    wp_cache_set('current_persona_' . $this->get_user_identifier(), $persona, 'personas', HOUR_IN_SECONDS);
    
    return $persona;
}
```

### Content Retrieval

Optimize content retrieval for persona-specific content:

```php
/**
 * Optimized persona content retrieval.
 *
 * @param int    $post_id   The post ID.
 * @param string $persona   The persona to get content for.
 * @return array            The persona content variations.
 */
public function get_optimized_persona_content($post_id, $persona) {
    // Generate cache key
    $cache_key = 'persona_variations_' . $post_id . '_' . $persona;
    
    // Try to get from cache
    $variations = wp_cache_get($cache_key, 'persona-content');
    if (false !== $variations) {
        return $variations;
    }
    
    // Get from database
    $variations = get_post_meta($post_id, '_persona_variations_' . $persona, true);
    if (!is_array($variations)) {
        $variations = array();
    }
    
    // Cache for future requests
    wp_cache_set($cache_key, $variations, 'persona-content', HOUR_IN_SECONDS);
    
    return $variations;
}
```

## Asset Loading

### Conditional Enqueuing

Only load assets when needed:

```php
/**
 * Conditionally enqueue assets.
 */
function cme_enqueue_persona_assets() {
    global $post;
    
    // Skip if not a singular post or no content
    if (!is_singular() || empty($post->post_content)) {
        return;
    }
    
    // Only load assets if shortcodes are present
    if (strpos($post->post_content, '[if_persona') !== false ||
        strpos($post->post_content, '[persona_') !== false) {
        
        wp_enqueue_style(
            'cme-personas',
            plugin_dir_url(CME_PERSONAS_PLUGIN_FILE) . 'public/css/personas.min.css',
            array(),
            CME_PERSONAS_VERSION
        );
        
        wp_enqueue_script(
            'cme-personas-js',
            plugin_dir_url(CME_PERSONAS_PLUGIN_FILE) . 'public/js/personas.min.js',
            array('jquery'),
            CME_PERSONAS_VERSION,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'cme_enqueue_persona_assets');
```

### Critical CSS

Inline critical CSS for faster rendering:

```php
/**
 * Add critical CSS for personas.
 */
function cme_add_persona_critical_css() {
    // Only add if personas are active
    if (!$this->is_persona_active()) {
        return;
    }
    
    // Critical CSS
    echo '<style>
    .persona-content { display: none; }
    .persona-content.active { display: block; }
    .persona-switcher { margin: 1em 0; }
    </style>';
}
add_action('wp_head', 'cme_add_persona_critical_css', 1);
```

## Implementation Phases

### Phase 1: Core Optimization

- [x] Implement selective shortcode processing
- [x] Optimize persona detection queries
- [x] Add conditional asset loading
- [x] Optimize database schema for persona content

### Phase 2: Redis Integration

- [ ] Install and configure Redis
- [ ] Implement content caching for processed shortcodes
- [ ] Cache persona detection results
- [ ] Optimize cache key generation and TTL

### Phase 3: Edge Caching

- [ ] Configure cache headers for personas
- [ ] Set up Cloudflare page rules
- [ ] Implement cache invalidation for content updates
- [ ] Add debug tools for cache verification

### Phase 4: Advanced Optimizations

- [ ] Implement fragment caching for complex page sections
- [ ] Add prefetching for common resources
- [ ] Optimize image loading for different personas
- [ ] Implement performance monitoring

## Monitoring and Profiling

### Performance Metrics

Monitor key metrics to ensure optimal performance:

```php
/**
 * Log persona performance metrics.
 */
function cme_log_persona_performance() {
    // Only log for a sample of requests
    if (mt_rand(1, 100) > 5) {
        return;
    }
    
    global $wpdb;
    
    // Get metrics
    $load_time = timer_stop(0, 3);
    $query_count = $wpdb->num_queries;
    $current_persona = cme_get_current_persona();
    
    // Log to custom table
    $wpdb->insert(
        $wpdb->prefix . 'persona_performance',
        array(
            'timestamp' => current_time('mysql'),
            'persona' => $current_persona,
            'load_time' => $load_time,
            'query_count' => $query_count,
            'page_id' => get_the_ID(),
            'url' => $_SERVER['REQUEST_URI'],
        )
    );
}
add_action('wp_footer', 'cme_log_persona_performance');
```

### Debug Logging

Add debug logging capabilities:

```php
/**
 * Log persona debug information.
 *
 * @param string $message  The debug message.
 * @param mixed  $data     Optional data to log.
 */
function cme_persona_debug_log($message, $data = null) {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    $log_entry = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    
    if ($data !== null) {
        $log_entry .= ': ' . print_r($data, true);
    }
    
    error_log($log_entry);
}
```

## Related Documentation

- [Architecture Overview](./01-architecture-overview.md) - High-level system architecture
- [Implementation Approach](./02-implementation-approach.md) - Implementation options and decisions
- [Technical Reference](./03-technical-reference.md) - Detailed code examples
