# Persona System Implementation

This document provides a comprehensive overview of the persona system implementation, including a comparison of different implementation approaches and the detailed implementation plan for the chosen standalone WordPress plugin architecture.

## Table of Contents

- [Persona System Implementation](#persona-system-implementation)
    - [Table of Contents](#table-of-contents)
    - [Implementation Approaches Comparison](#implementation-approaches-comparison)
        - [Introduction](#introduction)
        - [Option 1: Server-Side Persona Detection with Edge Caching](#option-1-server-side-persona-detection-with-edge-caching)
            - [Technical Implementation Details](#technical-implementation-details)
                - [WordPress/PHP Backend](#wordpressphp-backend)
                - [Cloudflare Configuration](#cloudflare-configuration)
            - [Performance Characteristics](#performance-characteristics)
            - [Scalability Factors](#scalability-factors)
            - [Specific Challenges](#specific-challenges)
        - [Option 3: Edge-Worker Persona Application](#option-3-edge-worker-persona-application)
            - [Technical Implementation Details](#technical-implementation-details-1)
                - [WordPress/PHP Backend](#wordpressphp-backend-1)
                - [Cloudflare Worker Implementation](#cloudflare-worker-implementation)
            - [Performance Characteristics](#performance-characteristics-1)
            - [Scalability Factors](#scalability-factors-1)
            - [Specific Challenges](#specific-challenges-1)
        - [Data Flow Comparison with 'easy-breezy' Example](#data-flow-comparison-with-easy-breezy-example)
            - [Option 1: Server-Side Flow](#option-1-server-side-flow)
            - [Option 3: Edge-Worker Flow](#option-3-edge-worker-flow)
        - [Decision Factors Summary](#decision-factors-summary)
    - [Chosen Implementation: WordPress Plugin](#chosen-implementation-wordpress-plugin)
        - [Overview](#overview)
        - [Plugin Architecture](#plugin-architecture)
        - [Phase 1: Core Infrastructure](#phase-1-core-infrastructure)
            - [Tasks](#tasks)
            - [Deliverables](#deliverables)
            - [Core Classes](#core-classes)
                - [Persona Manager Class](#persona-manager-class)
                - [Persona Content Class](#persona-content-class)

## Implementation Approaches Comparison

### Introduction

Both approaches assume that the Personas plugin handles core persona management, while the Cruise Made Easy plugin needs to integrate with this system to deliver personalized cruise content. The comparison focuses on technical implementation, performance, and integration with edge caching solutions like Cloudflare.

### Option 1: Server-Side Persona Detection with Edge Caching

#### Technical Implementation Details

##### WordPress/PHP Backend

1. **GTM Integration Layer**:

    ```php
    function cme_detect_persona_from_gtm() {
      // Read from $_COOKIE or GTM dataLayer variables
      $persona = isset($_COOKIE['_gtm_persona']) ? sanitize_key($_COOKIE['_gtm_persona']) : null;

      // Validate against allowed personas
      if (!in_array($persona, ['default', 'easy-breezy', 'luxe', 'thrill-seeker'])) {
        $persona = 'default';
      }

      return apply_filters('cme_current_persona', $persona);
    }
    ```

2. **Pre-Query Content Filtering**:

    ```php
    function cme_filter_query_for_persona($query) {
      if (!is_admin() && $query->is_main_query()) {
        $persona = cme_detect_persona_from_gtm();

        // Add meta query to filter content
        $query->set('meta_query', [
          [
            'key' => '_cme_persona',
            'value' => [$persona, 'default'],
            'compare' => 'IN'
          ]
        ]);
      }
      return $query;
    }
    add_action('pre_get_posts', 'cme_filter_query_for_persona');
    ```

3. **Content Storage Strategy**:

    - Store persona-specific content as post meta
    - Create separate posts for each persona variation with shared ID relationship
    - Use custom database tables optimized for persona lookups

4. **Cache Headers Management**:

    ```php
    function cme_set_persona_cache_headers() {
      $persona = cme_detect_persona_from_gtm();

      // Set Cloudflare cache headers
      header('Cache-Tag: persona-' . $persona);

      // Set Vary header to ensure proper caching
      header('Vary: Cookie');
    }
    add_action('send_headers', 'cme_set_persona_cache_headers');
    ```

##### Cloudflare Configuration

1. **Cache Rules**:

    - Create page rules that cache based on Cookie values
    - Configure Edge Cache TTL appropriate for content update frequency
    - Set up cache key modification to include persona information

2. **Example Cloudflare Page Rule**:

    ```yaml
    URL: *cruisemadeeasy.com/*
    Settings:
      - Cache Level: Everything
      - Edge Cache TTL: 2 hours
      - Cache Key: Include Cookie: _gtm_persona
    ```

#### Performance Characteristics

1. **Server Load**:

    - Higher than client-side approach because WordPress processes each persona variation separately
    - Lower than non-cached approaches since edges serve most requests
    - Database queries are optimized to fetch only relevant persona content

2. **TTFB (Time To First Byte)**:

    - 20-40ms for cached edge responses
    - 200-800ms for cache misses requiring origin fetch
    - Subsequent visits are extremely fast with pre-rendered correct content

3. **Bandwidth Usage**:
    - Optimal: only requested persona content is transferred
    - Typical page size reduction: 60-75% compared to multi-persona page

#### Scalability Factors

- **Content Volume Scale**: Excellent - each request only processes relevant content
- **Traffic Scale**: Excellent - most requests served from edge
- **Author Experience**: Good - authors need to be aware of content variations
- **Development Complexity**: Moderate - requires careful database design and query optimization

#### Specific Challenges

1. **Cache Purging Strategy**:
   When content for a specific persona is updated, you need a targeted cache invalidation strategy:

    ```php
    function cme_purge_persona_cache($post_id, $post) {
      $personas = ['default', 'easy-breezy', 'luxe', 'thrill-seeker'];

      foreach ($personas as $persona) {
        if (has_persona_content($post_id, $persona)) {
          // Purge specific persona cache
          cloudflare_purge_cache_tag("persona-{$persona}");
        }
      }
    }
    add_action('save_post', 'cme_purge_persona_cache', 10, 2);
    ```

2. **GTM Reliability**:
   GTM may not always be available on first request. A robust fallback mechanism is needed:

    ```php
    function cme_get_persona_with_fallback() {
      $persona = cme_detect_persona_from_gtm();

      // If no persona detected, check URL params
      if ($persona === 'default' && isset($_GET['persona'])) {
        $persona = sanitize_key($_GET['persona']);
      }

      // If still not set or invalid, check user meta if logged in
      if (!in_array($persona, ['easy-breezy', 'luxe', 'thrill-seeker'])) {
        if (is_user_logged_in()) {
          $user_persona = get_user_meta(get_current_user_id(), 'preferred_persona', true);
          if (!empty($user_persona)) {
            $persona = $user_persona;
          }
        }
      }

      return $persona;
    }
    ```

### Option 3: Edge-Worker Persona Application

#### Technical Implementation Details

##### WordPress/PHP Backend

1. **Content Delivery Structure**:

    ```php
    function cme_wrap_content_with_persona_markers($content) {
      // Get all persona variations for this content
      $default_content = $content;
      $easy_breezy = get_post_meta(get_the_ID(), '_cme_persona_easy_breezy_content', true);
      $luxe = get_post_meta(get_the_ID(), '_cme_persona_luxe_content', true);
      $thrill = get_post_meta(get_the_ID(), '_cme_persona_thrill_content', true);

      // Build HTML with all variations
      $html = '';
      $html .= '<div data-persona="default">' . $default_content . '</div>';

      if (!empty($easy_breezy)) {
        $html .= '<div data-persona="easy-breezy">' . $easy_breezy . '</div>';
      }

      if (!empty($luxe)) {
        $html .= '<div data-persona="luxe">' . $luxe . '</div>';
      }

      if (!empty($thrill)) {
        $html .= '<div data-persona="thrill-seeker">' . $thrill . '</div>';
      }

      return $html;
    }
    add_filter('the_content', 'cme_wrap_content_with_persona_markers');
    ```

2. **Cache Headers for Edge Worker**:

    ```php
    function cme_add_edge_compatible_headers() {
      // Add custom header to signal persona content to edge
      header('X-Has-Persona-Content: true');

      // Set cache control appropriately
      header('Cache-Control: public, max-age=3600');
    }
    add_action('send_headers', 'cme_add_edge_compatible_headers');
    ```

##### Cloudflare Worker Implementation

1. **Worker JavaScript Code**:

    ```javascript
    addEventListener("fetch", (event) => {
    	event.respondWith(handleRequest(event.request));
    });

    async function handleRequest(request) {
    	// Extract persona from cookies or other sources
    	const persona = getPersonaFromRequest(request) || "default";

    	// Get the response from origin or cache
    	const response = await fetch(request);

    	// Check if this page has persona content
    	const hasPersonaContent = response.headers.get("X-Has-Persona-Content");

    	if (hasPersonaContent !== "true") {
    		// No persona content, return response as is
    		return response;
    	}

    	// Get the HTML text
    	const html = await response.text();

    	// Process the HTML for this persona
    	const processedHtml = processHtmlForPersona(html, persona);

    	// Create a new response
    	const newResponse = new Response(processedHtml, {
    		status: response.status,
    		statusText: response.statusText,
    		headers: response.headers,
    	});

    	// Add cache headers for this persona
    	newResponse.headers.set("Cache-Tag", `persona-${persona}`);

    	return newResponse;
    }

    function processHtmlForPersona(html, persona) {
    	// Simple regex approach (actual implementation would use HTML parser)
    	const regex = new RegExp(
    		`<div data-persona="${persona}">([\\s\\S]*?)<\/div>`,
    		"g",
    	);
    	let processedHtml = html;

    	// Remove all persona containers
    	processedHtml = processedHtml.replace(
    		/<div data-persona="[^"]*">[\s\S]*?<\/div>/g,
    		"",
    	);

    	// Find all content for this persona
    	const matches = [...html.matchAll(regex)];

    	// Replace back the content for this persona
    	matches.forEach((match) => {
    		// Find position to insert content by looking for markers
    		// This is a simplified approach - real implementation would be more robust
    		processedHtml = processedHtml.replace(
    			"<!-- PERSONA_CONTENT_MARKER -->",
    			match[1],
    		);
    	});

    	return processedHtml;
    }

    function getPersonaFromRequest(request) {
    	// Get cookies from request
    	const cookieHeader = request.headers.get("Cookie") || "";
    	const cookies = cookieHeader.split(";").reduce((obj, c) => {
    		const [key, value] = c.trim().split("=");
    		obj[key] = value;
    		return obj;
    	}, {});

    	return cookies["_gtm_persona"];
    }
    ```

2. **Enhanced HTML Parser**:
   For production, you'd want to use a proper HTML parser rather than regex:

    ```javascript
    // Using HTMLRewriter (Cloudflare's recommended approach)
    async function handleRequest(request) {
    	const persona = getPersonaFromRequest(request) || "default";
    	const response = await fetch(request);

    	if (response.headers.get("X-Has-Persona-Content") !== "true") {
    		return response;
    	}

    	return new HTMLRewriter()
    		.on("div[data-persona]", {
    			element(el) {
    				const elPersona = el.getAttribute("data-persona");

    				// If this isn't for our persona, remove it
    				if (elPersona !== persona && elPersona !== "default") {
    					el.remove();
    				} else {
    					// Keep the content but remove the data-persona attribute
    					el.removeAttribute("data-persona");
    				}
    			},
    		})
    		.transform(response);
    }
    ```

#### Performance Characteristics

1. **Server Load**:

    - Lower than Option 1 because the same content (all personas) is served for all requests
    - Database access pattern is simpler (no complex persona filtering)
    - WordPress does more work upfront generating all variations

2. **TTFB (Time To First Byte)**:

    - 50-70ms for edge-processed cached responses
    - 250-850ms for cache misses requiring origin fetch
    - Slight processing overhead for HTML transformation at the edge

3. **Bandwidth Usage (Origin to Edge)**:
    - Higher than Option 1: all persona content is transferred to edge
    - Typical multiplier: 2.5-4x the size of a single-persona page
    - Bandwidth from edge to client remains optimal

#### Scalability Factors

- **Content Volume Scale**: Good - edge handles transformation of increasing content volumes
- **Traffic Scale**: Excellent - most requests served from edge
- **Author Experience**: Excellent - content variations are managed in one place
- **Development Complexity**: Higher - requires Cloudflare Worker expertise

#### Specific Challenges

1. **HTML Parsing Complexity**:
   HTML transformation at edge requires careful handling of nested structures and special cases:

    ```javascript
    // Handle edge cases with nested persona content
    .on('div[data-persona] div[data-persona]', {
      element(el) {
        // Handle nested persona markers
        const parent = el.parentElement?.getAttribute('data-persona');
        const current = el.getAttribute('data-persona');

        // Complex rules for nested persona content...
      }
    })
    ```

2. **Worker Resource Limits**:
   Cloudflare Workers have CPU time limits. For very large pages:

    ```javascript
    // Implement streaming processing for large pages
    async function processLargePage(response, persona) {
    	const { readable, writable } = new TransformStream();

    	// Create a streaming HTML processor
    	const htmlProcessor = new HTMLProcessor(persona);

    	// Process the stream
    	response.body
    		.pipeThrough(new TextDecoderStream())
    		.pipeThrough(htmlProcessor)
    		.pipeThrough(new TextEncoderStream())
    		.pipeTo(writable);

    	return new Response(readable, response);
    }
    ```

### Data Flow Comparison with 'easy-breezy' Example

#### Option 1: Server-Side Flow

1. User with GTM tag 'easy-breezy' visits a Caribbean cruise page
2. Request hits Cloudflare edge
3. Edge reads `_gtm_persona=easy-breezy` cookie
4. **If cached**: Edge serves pre-rendered 'easy-breezy' version (DONE)
5. **If not cached**: Request goes to WordPress with cookie
6. WordPress reads cookie, sets `$current_persona = 'easy-breezy'`
7. WordPress queries cruise content: `SELECT * FROM posts JOIN postmeta WHERE post_id=123 AND meta_key='_cme_persona' AND meta_value='easy-breezy'`
8. If no easy-breezy content found, falls back to default
9. WordPress renders ONLY relevant content
10. Response sent to Cloudflare, which caches it with persona key
11. User receives page with only 'easy-breezy' content

#### Option 3: Edge-Worker Flow

1. User with GTM tag 'easy-breezy' visits a Caribbean cruise page
2. Request hits Cloudflare edge
3. Edge reads `_gtm_persona=easy-breezy` cookie
4. **If cached**: Edge serves pre-transformed 'easy-breezy' version (DONE)
5. **If not cached**: Request goes to WordPress (no persona info needed)
6. WordPress renders page with ALL persona variations in it:

    ```html
    <div data-persona="default">Default cruise description...</div>
    <div data-persona="easy-breezy">Relaxed, simple cruise description...</div>
    <div data-persona="luxe">
    	Premium cruise description with luxury details...
    </div>
    <!-- more content with persona variations -->
    ```

7. Response returns to Cloudflare edge
8. Edge Worker processes HTML, keeping only 'easy-breezy' and fallback 'default' content
9. Transformed HTML is cached at edge with persona key
10. User receives page with only 'easy-breezy' content

### Decision Factors Summary

| Factor                  | Option 1: Server-Side                               | Option 3: Edge-Worker                               |
| ----------------------- | --------------------------------------------------- | --------------------------------------------------- |
| **Development Effort**  | Moderate PHP/WordPress development                  | PHP development + Cloudflare Worker expertise       |
| **Content Management**  | Standard WordPress editing with persona meta fields | Same WordPress editing experience                   |
| **Origin Bandwidth**    | Lower (only requested persona sent)                 | Higher (all personas sent to edge)                  |
| **Origin Server Load**  | Moderate (persona-specific DB queries)              | Lower (simpler queries, more content)               |
| **Page Size to Client** | Optimal - only contains requested persona           | Optimal - transformed at edge                       |
| **Cache Efficiency**    | Very good - separate cache entries per persona      | Very good - separate transformed cache entries      |
| **Time to Market**      | Faster - uses WordPress-native techniques           | Slightly longer - requires edge worker development  |
| **Maintenance**         | Concentrated in WordPress                           | Split between WordPress and Cloudflare              |
| **Flexibility**         | Good - easy to add new processing in WordPress      | Excellent - processing can happen at edge or origin |

## Chosen Implementation: WordPress Plugin

Based on the comparison of different approaches, we've decided to implement personas as a standalone WordPress plugin using the Server-Side approach (Option 1). This approach offers better separation of concerns, modularity, and reusability, while still providing excellent performance through proper edge caching.

### Overview

The Personas plugin will provide a comprehensive system for creating and managing persona-specific content across any WordPress site. It will use structured object storage with post meta, and feature a user-friendly editing interface integrated with the Gutenberg block editor.

Key features:

- Persona detection from GTM/cookies
- Content storage and retrieval system
- Admin UI for managing persona content
- Frontend content replacement
- Optional Redis caching for performance

### Plugin Architecture

The plugin will follow WordPress best practices with a modular architecture:

```php
wp-content/plugins/personas/
├── personas.php                    # Main plugin file
├── includes/                       # Core functionality
│   ├── class-personas.php          # Main plugin class
│   ├── class-activator.php         # Activation hooks
│   ├── class-deactivator.php       # Deactivation hooks
│   ├── class-persona-manager.php   # Persona management
│   ├── class-persona-content.php   # Content storage/retrieval
│   └── class-persona-cache.php     # Optional Redis caching
├── admin/                          # Admin functionality
│   ├── class-personas-admin.php    # Admin functionality
│   ├── js/
│   │   └── personas-admin.js       # Admin JavaScript
│   ├── css/
│   │   └── personas-admin.css      # Admin styles
│   └── partials/                   # Admin view templates
├── public/                         # Frontend functionality
│   ├── class-personas-public.php   # Public functionality
│   ├── js/
│   │   └── personas-public.js      # Public JavaScript
│   └── css/
│       └── personas-public.css     # Public styles
└── languages/                      # Internationalization
```

### Phase 1: Core Infrastructure

#### Tasks

1. **Create Plugin Scaffold**

    - Set up plugin directory structure
    - Create main plugin file with metadata
    - Implement activation/deactivation hooks

2. **Create Persona Management Class**

    - Define available personas
    - Implement persona detection from GTM/cookies
    - Add functions to get/set current persona

3. **Implement Storage System**
    - Create functions to save/retrieve persona variations
    - Define schema for structured content storage
    - Add metadata registration for persona content

#### Deliverables

- Complete plugin scaffold
- Core classes for persona management
- Content storage and retrieval system

#### Core Classes

##### Persona Manager Class

```php
/**
 * Class Personas_Manager
 *
 * Handles persona detection, management, and context.
 */
class Personas_Manager {
    /**
     * Singleton instance.
     *
     * @var Personas_Manager
     */
    private static $instance = null;

    /**
     * Available personas.
     *
     * @var array
     */
    private $personas = [];

    /**
     * Current persona.
     *
     * @var string
     */
    private $current_persona = 'default';

    /**
     * Get singleton instance.
     *
     * @return Personas_Manager
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        // Load available personas
        $this->load_personas();

        // Detect current persona
        add_action('init', array($this, 'detect_persona'), 5);
    }

    /**
     * Load available personas.
     */
    private function load_personas() {
        // Default personas
        $this->personas = [
            'default' => __('Default', 'personas'),
            'easy-breezy' => __('Easy-Breezy Cruiser', 'personas'),
            'luxe' => __('Luxe Seafarer', 'personas'),
            'thrill-seeker' => __('Thrill Seeker', 'personas')
        ];

        // Allow modification via filter
        $this->personas = apply_filters('personas_available_personas', $this->personas);
    }

    /**
     * Get available personas.
     *
     * @return array
     */
    public function get_available_personas() {
        return $this->personas;
    }

    /**
     * Detect current persona from GTM/cookies/URL.
     */
    public function detect_persona() {
        $persona = 'default';

        // Try cookie first
        if (isset($_COOKIE['_gtm_persona'])) {
            $cookie_persona = sanitize_key($_COOKIE['_gtm_persona']);
            if ($this->is_valid_persona($cookie_persona)) {
                $persona = $cookie_persona;
            }
        }

        // Try URL parameter
        if (isset($_GET['persona'])) {
            $url_persona = sanitize_key($_GET['persona']);
            if ($this->is_valid_persona($url_persona)) {
                $persona = $url_persona;

                // Set cookie for future requests
                if (!headers_sent()) {
                    setcookie('_gtm_persona', $persona, time() + 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
                }
            }
        }

        // Allow custom detection via filter
        $persona = apply_filters('personas_detected_persona', $persona);

        // Set current persona
        $this->current_persona = $persona;

        // Maybe set up data layer for GTM
        if (!is_admin()) {
            add_action('wp_head', array($this, 'maybe_setup_gtm_datalayer'), 1);
        }
    }

    /**
     * Check if a persona is valid.
     *
     * @param string $persona Persona to check.
     * @return bool
     */
    public function is_valid_persona($persona) {
        return array_key_exists($persona, $this->personas);
    }

    /**
     * Get current persona.
     *
     * @return string
     */
    public function get_current_persona() {
        return $this->current_persona;
    }

    /**
     * Set current persona.
     *
     * @param string $persona Persona to set.
     * @return bool Success.
     */
    public function set_current_persona($persona) {
        if ($this->is_valid_persona($persona)) {
            $this->current_persona = $persona;
            return true;
        }
        return false;
    }

    /**
     * Maybe set up GTM data layer.
     */
    public function maybe_setup_gtm_datalayer() {
        // Only if GTM integration is enabled
        if (!get_option('personas_gtm_integration', true)) {
            return;
        }

        // Output data layer
        ?>
        <script>
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'persona': '<?php echo esc_js($this->current_persona); ?>'
        });
        </script>
        <?php
    }
}
```

##### Persona Content Class

```php
/**
 * Class Personas_Content
 *
 * Handles storage and retrieval of persona-specific content.
 */
class Personas_Content {
    /**
     * Singleton instance.
     *
     * @var Personas_Content
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return Personas_Content
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        // Set up content filters
        add_action('init', array($this, 'setup_content_filters'));
    }

    /**
     * Set up content filters.
     */
    public function setup_content_filters() {
        // Skip in admin
        if (is_admin()) {
            return;
        }

        // Main content filter
        add_filter('the_content', array($this, 'filter_content'), 10, 1);

        // Title filter
        add_filter('the_title', array($this, 'filter_title'), 10, 2);

        // Featured image filter
        add_filter('post_thumbnail_id', array($this, 'filter_featured_image'), 10, 2);
    }

    /**
     * Save persona variations for a post.
     *
     * @param int $post_id The post ID.
     * @param string $persona The persona identifier.
     * @param array $variations Content variations.
     * @return bool Success.
     */
    public function save_variations($post_id, $persona, $variations) {
        // Validate inputs
        if (empty($post_id) || empty($persona)) {
            return false;
        }

        // Ensure we have array of variations
        if (!is_array($variations)) {
            return false;
        }

        // Generate meta key
        $meta_key = "_personas_{$persona}_variations";

        // Save variations
        return update_post_meta($post_id, $meta_key, $variations);
    }

    /**
     * Get persona variations for a post.
     *
     * @param int $post_id The post ID.
     * @param string $persona The persona identifier.
     * @return array Content variations.
     */
    public function get_variations($post_id, $persona) {
        // Generate meta key
        $meta_key = "_personas_{$persona}_variations";

        // Get variations
        $variations = get_post_meta($post_id, $meta_key, true);

        // Ensure we return an array
        return is_array($variations) ? $variations : [];
    }

    /**
     * Filter content for current persona.
     *
     * @param string $content The content.
     * @return string Filtered content.
     */
    public function filter_content($content) {
        // Get current post ID
        $post_id = get_the_ID();
        if (!$post_id) {
            return $content;
        }

        // Get current persona
        $persona_manager = Personas_Manager::get_instance();
        $persona = $persona_manager->get_current_persona();

        // Skip for default persona
        if ($persona === 'default') {
            return $content;
        }

        // Get variations
        $variations = $this->get_variations($post_id, $persona);
        if (empty($variations)) {
            return $content;
        }

        // Parse blocks
        $blocks = parse_blocks($content);

        // Apply variations
        $blocks = $this->apply_variations_to_blocks($blocks, $variations);

        // Return modified content
        return serialize_blocks($blocks);
    }

    /**
     * Apply variations to parsed blocks.
     *
     * @param array $blocks Parsed blocks.
     * @param array $variations Content variations.
     * @return array Modified blocks.
     */
    private function apply_variations_to_blocks($blocks, $variations) {
        foreach ($blocks as &$block) {
            // Check if we have a block ID
            if (!empty($block['attrs']['id']) && isset($variations[$block['attrs']['id']])) {
                $block_id = $block['attrs']['id'];
                $variation = $variations[$block_id];

                // Apply variation based on block type
                $this->apply_variation_to_block($block, $variation);
            }

            // Process inner blocks recursively
            if (!empty($block['innerBlocks'])) {
                $block['innerBlocks'] = $this->apply_variations_to_blocks(
                    $block['innerBlocks'],
                    $variations
                );
            }
        }

        return $blocks;
    }

    /**
     * Apply variation to a specific block.
     *
     * @param array &$block Block to modify.
     * @param mixed $variation Variation data.
     */
    private function apply_variation_to_block(&$block, $variation) {
        switch ($block['blockName']) {
            case 'core/paragraph':
                // For paragraphs, update content
                $block['attrs']['content'] = $variation;
                if (isset($block['innerHTML'])) {
                    $block['innerHTML'] = wpautop($variation);
                }
                break;

            case 'core/heading':
                // For headings, update content
                $block['attrs']['content'] = $variation;
                if (isset($block['innerHTML'])) {
                    $level = $block['attrs']['level'] ?? 2;
                    $block['innerHTML'] = "<h{$level}>{$variation}</h{$level}>";
                }
                break;

            case '
```
