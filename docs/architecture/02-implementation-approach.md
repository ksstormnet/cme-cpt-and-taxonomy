# Persona System Implementation Approach

## Version Information

Current as of v1.5.1

This document provides a comprehensive overview of the persona system implementation approach, including a comparison of different implementation options and the rationale for choosing the boundary-based shortcode method.

## Table of Contents

- [Persona System Implementation Approach](#persona-system-implementation-approach)
  - [Version Information](#version-information)
  - [Table of Contents](#table-of-contents)
  - [Implementation Decision Summary](#implementation-decision-summary)
  - [Implementation Approaches Comparison](#implementation-approaches-comparison)
    - [Introduction](#introduction)
    - [Option 1: Server-Side Persona Detection with Edge Caching](#option-1-server-side-persona-detection-with-edge-caching)
      - [Technical Implementation Details](#technical-implementation-details)
      - [Performance Characteristics](#performance-characteristics)
      - [Scalability Factors](#scalability-factors)
      - [Specific Challenges](#specific-challenges)
    - [Option 2: Edge-Worker Persona Application](#option-2-edge-worker-persona-application)
      - [Technical Implementation Details](#technical-implementation-details-1)
      - [Performance Characteristics](#performance-characteristics-1)
      - [Scalability Factors](#scalability-factors-1)
      - [Specific Challenges](#specific-challenges-1)
    - [Data Flow Comparison with 'easy-breezy' Example](#data-flow-comparison-with-easy-breezy-example)
      - [Option 1: Server-Side Flow](#option-1-server-side-flow)
      - [Option 2: Edge-Worker Flow](#option-2-edge-worker-flow)
    - [Decision Factors Summary](#decision-factors-summary)
  - [Chosen Implementation: WordPress Plugin with Boundary-Based Shortcodes](#chosen-implementation-wordpress-plugin-with-boundary-based-shortcodes)
    - [Overview](#overview)
    - [Plugin Architecture](#plugin-architecture)
    - [Implementation Phases](#implementation-phases)
      - [Phase 1: Core Infrastructure](#phase-1-core-infrastructure)
      - [Phase 2: Shortcode System](#phase-2-shortcode-system)
      - [Phase 3: Admin Integration](#phase-3-admin-integration)
      - [Phase 4: Performance Optimization](#phase-4-performance-optimization)
    - [Key Technical Decisions](#key-technical-decisions)
  - [Related Documentation](#related-documentation)

## Implementation Decision Summary

After evaluating multiple implementation options, we've chosen the **boundary-based shortcode approach** for the Personas plugin implementation. This approach:

- Uses shortcodes to define conditional content boundaries
- Maintains compatibility with third-party plugins and content
- Offers superior flexibility for content authors
- Enables robust performance optimization through caching

The implementation is structured as a standalone WordPress plugin rather than being embedded within the Cruise Made Easy plugin. This provides better separation of concerns, modularity, and reusability.

## Implementation Approaches Comparison

### Introduction

We evaluated two primary approaches for implementing the persona content system. Both assume that the Personas plugin handles core persona management, but they differ in how content is processed and delivered.

### Option 1: Server-Side Persona Detection with Edge Caching

#### Technical Implementation Details

**WordPress/PHP Backend**:

- Persona detection via cookies, URL parameters, or user meta
- Content filtering at the server level with WordPress hooks
- Database queries optimized to fetch only relevant persona content
- Edge cache integration via custom headers

**Example - Persona Detection**:

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

**Content Storage Strategy**:

- Store persona-specific content within shortcode boundaries
- Process shortcodes server-side to include or exclude content
- Use WordPress's native shortcode system for compatibility

**Cache Integration**:

- Set persona-specific cache headers
- Configure edge caching to vary by persona

#### Performance Characteristics

1. **Server Load**:

   - Higher than client-side approach but optimized through strategic shortcode processing
   - Lower than non-cached approaches through edge caching

2. **TTFB (Time To First Byte)**:

   - 20-40ms for cached edge responses
   - 200-800ms for cache misses requiring origin fetch

3. **Bandwidth Usage**:
   - Optimal: only requested persona content is transferred
   - Typical page size reduction: 60-75% compared to multi-persona page

#### Scalability Factors

- **Content Volume Scale**: Excellent - each request only processes relevant content
- **Traffic Scale**: Excellent - most requests served from edge
- **Author Experience**: Good - intuitive shortcode system for content authors
- **Development Complexity**: Moderate - uses familiar WordPress patterns

#### Specific Challenges

1. **Cache Management**:

   - Requires careful cache invalidation when persona content changes
   - Need to set proper cache headers for edge caching

2. **Fallback Mechanism**:
   - Needs robust handling when GTM/cookies aren't available
   - Hierarchical detection system with multiple fallbacks

### Option 2: Edge-Worker Persona Application

#### Technical Implementation Details

**WordPress/PHP Backend**:

- Deliver all persona variations to the edge
- Use HTML data attributes to mark persona-specific content
- Process content at the edge level rather than server

**Content Delivery Structure**:

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

  // Add other persona content
  return $html;
}
```

**Edge Processing**:

- Cloudflare Workers or similar edge computing platform
- HTML transformation to remove non-matching persona content
- Caching of transformed content at the edge

#### Performance Characteristics

1. **Server Load**:

   - Lower than Option 1 because the same content is served for all requests
   - Higher bandwidth between origin and edge

2. **TTFB (Time To First Byte)**:

   - 50-70ms for edge-processed cached responses
   - 250-850ms for cache misses requiring origin fetch

3. **Bandwidth Usage (Origin to Edge)**:
   - Higher than Option 1: all persona content is transferred to edge
   - Bandwidth from edge to client remains optimal

#### Scalability Factors

- **Content Volume Scale**: Good - edge handles transformation of increasing content volumes
- **Traffic Scale**: Excellent - most requests served from edge
- **Author Experience**: Excellent - content variations are managed in one place
- **Development Complexity**: Higher - requires edge computing expertise

#### Specific Challenges

1. **HTML Transformation Complexity**:

   - Requires careful handling of nested structures
   - Edge computing platforms have resource limits

2. **Debugging Difficulty**:
   - Issues may occur at edge level, harder to diagnose
   - Requires expertise in both WordPress and edge computing

### Data Flow Comparison with 'easy-breezy' Example

#### Option 1: Server-Side Flow

1. User with 'easy-breezy' persona visits a page
2. Request hits Cloudflare edge
3. Edge reads persona cookie
4. If cached: Edge serves pre-rendered 'easy-breezy' version
5. If not cached: Request goes to WordPress with cookie
6. WordPress processes shortcodes, showing only 'easy-breezy' content
7. Response sent to Cloudflare, which caches it with persona key
8. User receives page with only 'easy-breezy' content

#### Option 2: Edge-Worker Flow

1. User with 'easy-breezy' persona visits a page
2. Request hits Cloudflare edge
3. Edge reads persona cookie
4. If cached: Edge serves pre-transformed 'easy-breezy' version
5. If not cached: Request goes to WordPress (no persona info needed)
6. WordPress renders page with ALL persona variations in it
7. Response returns to Cloudflare edge
8. Edge Worker processes HTML, keeping only 'easy-breezy' content
9. Transformed HTML is cached at edge with persona key
10. User receives page with only 'easy-breezy' content

### Decision Factors Summary

| Factor                  | Option 1: Server-Side                          | Option 2: Edge-Worker                          |
| ----------------------- | ---------------------------------------------- | ---------------------------------------------- |
| **Development Effort**  | Moderate PHP/WordPress development             | PHP development + Edge computing expertise     |
| **Content Management**  | Intuitive shortcode-based editing              | More complex markup with data attributes       |
| **Origin Bandwidth**    | Lower (only requested persona sent)            | Higher (all personas sent to edge)             |
| **Origin Server Load**  | Moderate (shortcode processing)                | Lower (simpler queries, more content)          |
| **Page Size to Client** | Optimal - only contains requested persona      | Optimal - transformed at edge                  |
| **Cache Efficiency**    | Very good - separate cache entries per persona | Very good - separate transformed cache entries |
| **Time to Market**      | Faster - uses WordPress-native techniques      | Longer - requires edge worker development      |
| **Maintenance**         | Concentrated in WordPress                      | Split between WordPress and edge platform      |

## Chosen Implementation: WordPress Plugin with Boundary-Based Shortcodes

Based on the comparison, we've chosen the **Server-Side approach with boundary-based shortcodes** (Option 1).

### Overview

The Personas plugin provides a comprehensive system for creating and managing persona-specific content using intuitive shortcodes. Key features:

- Boundary-based shortcode system (`[if_persona]`)
- Persona detection from GTM/cookies/URL parameters
- Admin UI for previewing content per persona
- Performance optimization through caching

### Plugin Architecture

The plugin follows WordPress best practices with a modular architecture:

```text
wp-content/plugins/personas/
├── personas.php                    # Main plugin file
├── includes/                       # Core functionality
│   ├── class-personas.php          # Main plugin class
│   ├── class-activator.php         # Activation hooks
│   ├── class-deactivator.php       # Deactivation hooks
│   ├── class-persona-manager.php   # Persona management
│   ├── class-shortcodes.php        # Shortcode processing
│   └── class-persona-cache.php     # Optional Redis caching
├── admin/                          # Admin functionality
├── public/                         # Frontend functionality
└── languages/                      # Internationalization
```

### Implementation Phases

#### Phase 1: Core Infrastructure

- [ ] Create Plugin Scaffold
- [ ] Create Persona Management Class
- [ ] Implement Storage System

#### Phase 2: Shortcode System

- [ ] Implement Frontend class with shortcode handlers
- [ ] Create if_persona shortcode for boundary-based content
- [ ] Add persona_switcher shortcode
- [ ] Ensure compatibility with third-party shortcodes
- [ ] Add shortcode documentation

#### Phase 3: Admin Integration

- [ ] Create admin sidebar component
- [ ] Implement admin preview URL generation
- [ ] Add TinyMCE shortcode buttons
- [ ] Create admin notification system
- [ ] Add inline documentation

#### Phase 4: Performance Optimization

- [ ] Add shortcode detection optimization
- [ ] Implement Redis content caching for personalized content
- [ ] Add compatibility fixes for common plugins
- [ ] Create admin tools for performance monitoring

### Key Technical Decisions

1. **Boundary-Based Shortcode Approach**:

   - Clear demarcation of conditional content
   - Compatible with third-party plugins
   - Intuitive for content authors

2. **Persona Detection Strategy**:

   - Multiple detection methods (cookie, URL, user meta)
   - Hierarchical fallback system
   - WordPress filter for custom detection methods

3. **Performance Optimization**:
   - Conditional shortcode processing
   - Edge caching integration
   - Optional Redis object caching

## Related Documentation

- [Architecture Overview](./01-architecture-overview.md) - High-level system architecture
- [Technical Reference](./03-technical-reference.md) - Detailed code examples
- [Performance Optimization](./04-performance-optimization.md) - Redis caching and other optimizations
