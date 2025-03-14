# Persona Content System Architecture Overview

*Current as of v1.5.0*

This document provides a comprehensive technical overview of the CME Personas plugin architecture, explaining how the shortcode-based persona system works, its components, and how it can be extended.

## What is the Persona System?

The persona system enables the creation and delivery of tailored content for different visitor types using a boundary-based shortcode approach. Each visitor is identified as belonging to a specific "persona" or visitor archetype, and content is dynamically shown or hidden based on the active persona.

### Supported Personas

The system supports multiple personas, with common examples including:

1. **Default** - Standard content shown to unidentified visitors
2. **Easy-Breezy** - Casual visitors looking for simplicity
3. **Luxe** - Luxury-focused visitors seeking premium experiences
4. **Thrill Seeker** - Adventure-oriented visitors interested in activities

## System Architecture

### Component Diagram

```
┌──────────────────────────────────────────────────────────┐
│                   WordPress Frontend                     │
└───────────────────────────┬──────────────────────────────┘
                            │
             ┌──────────────▼──────────────┐
             │    Persona Content System    │
             └──────────────┬──────────────┘
                            │
    ┌─────────────┬─────────┴────────┬───────────────┐
    │             │                  │               │
┌───▼─────┐  ┌────▼─────┐  ┌─────────▼────┐  ┌───────▼───────┐
│ Persona │  │ Shortcode│  │  Persona     │  │ Admin         │
│ Manager │  │ Engine   │  │  Detection   │  │ Integration   │
└───┬─────┘  └────┬─────┘  └─────────┬────┘  └───────┬───────┘
    │             │                  │               │
    │       ┌─────▼─────┐       ┌────▼──────┐  ┌─────▼─────┐
    └───────► Content   │       │Cookie/URL │  │  Preview  │
            │ Filtering │       │Management │  │  Tools    │
            └───────────┘       └───────────┘  └───────────┘
```

### Key Components

The persona system consists of these main components:

1. **Persona Management** - Custom post type for defining personas
2. **Shortcode Processing** - Boundary-based conditional content handling
3. **Persona Detection** - Identification and tracking of the current persona
4. **Admin Integration** - Tools for previewing and testing persona content

### Technical Approach

The shortcode-based architecture was chosen for several benefits:

1. **Simplified Implementation** - Clear boundaries for conditional content
2. **Third-Party Compatibility** - Works with other plugins and complex content elements
3. **Performance** - No duplicate content storage, minimal database overhead
4. **Content Flexibility** - Works in all content areas without specialized editors

## Shortcode System

### Core Shortcode: `[if_persona]`

The primary component of the system is the `[if_persona]` shortcode, which creates boundaries around content that should be conditionally displayed based on the active persona:

```html
[if_persona is="persona-id"]
Content for this persona
[/if_persona]
```

The shortcode processor evaluates the current visitor's persona against the shortcode parameters and either displays or hides the enclosed content.

### Parameter Options

- `is="persona-id"` - Show content only for specified personas (comma-separated)
- `not="persona-id"` - Show content for all personas EXCEPT those specified

### Nesting Support

The shortcode system supports nesting for complex conditional logic:

```html
[if_persona not="thrill"]
  [if_persona is="luxe"]
    Luxury-specific content
  [/if_persona]
[/if_persona]
```

## Persona Detection

### Detection Methods

The system detects visitor personas through several methods:

1. **Cookie-Based**: Stores visitor's selected persona in a cookie
2. **URL Parameter**: Allows specifying persona via `?persona=xyz` parameter
3. **Session-Based**: Maintains persona selection across page views
4. **Default Fallback**: Uses configured default persona when none is detected

### Detection Flow

1. Check for URL parameter (`?persona=xyz`)
2. If not found, check for cookie
3. If not found, use default persona

## Admin Integration

### Preview System

The admin integration includes:

1. **Sidebar Controls**: Persona selector in the admin sidebar
2. **Query Parameter Preview**: Support for `?persona=xyz` in preview mode
3. **Content Testing Tools**: Visual indicators for different persona views

## Performance Considerations

### Shortcode Processing

The shortcode approach is optimized for performance:

1. **On-Demand Processing**: Content is processed at render time without duplicating storage
2. **Minimal Database Impact**: No additional database operations for content storage
3. **Caching Compatible**: Works with standard WordPress caching solutions and Redis

### Frontend Assets

Frontend assets are loaded only when needed:

1. **Conditional Enqueuing**: CSS/JS only loaded when shortcodes are present
2. **Minified Resources**: Optimized asset delivery
3. **Async Loading**: Non-blocking script loading

## Extension Points

### Available Hooks

Developers can extend the persona system through various hooks:

```php
// Filter the detected persona
add_filter('cme_current_persona', function($persona) {
    // Custom logic
    return $persona;
});

// Action when persona changes
add_action('cme_persona_switched', function($new_persona, $old_persona) {
    // Custom logic
});
```

### Integration Examples

The persona system can be integrated with other systems:

1. **Analytics Integration**: Track persona selections and engagement
2. **CRM Integration**: Sync persona preferences with customer profiles
3. **Membership Integration**: Connect personas with membership levels

## Future Enhancements

Planned improvements to the architecture include:

1. **Visual Editor Integration**: Improved shortcode insertion tools
2. **Performance Monitoring**: Tools for measuring persona content impact
3. **A/B Testing**: Split testing different persona content variations
4. **Machine Learning**: Automated persona detection based on user behavior

## Related Documentation

For more detailed information, see:

- [Implementation Approach](./02-implementation-approach.md) - Detailed comparison of implementation approaches
- [Technical Reference](./03-technical-reference.md) - Developer-focused code examples
- [Performance Optimization](./04-performance-optimization.md) - Performance strategies including Redis caching
