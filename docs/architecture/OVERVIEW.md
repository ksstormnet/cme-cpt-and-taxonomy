# Persona Content System Guide

This guide provides a comprehensive overview of the CME Personas plugin explaining how it works, how to use it, and how to extend it.

## What is the Persona System?

The persona system enables the creation and delivery of tailored content for different visitor types. Each visitor is identified as belonging to a specific "persona" or visitor archetype, and content is dynamically customized to match their preferences and needs.

### Supported Personas

The system supports multiple personas, with common examples including:

1. **Default** - Standard content shown to unidentified visitors
2. **Easy-Breezy** - Casual visitors looking for simplicity
3. **Luxe** - Luxury-focused visitors seeking premium experiences
4. **Thrill Seeker** - Adventure-oriented visitors interested in activities

## Content Management

### Admin Interface

The persona content system provides a tabbed interface in the WordPress admin for managing content variations.

Each tab contains a full Gutenberg block editor instance allowing for complete flexibility in creating persona-specific content.

### Key Features

- **Persona Management**: Create and manage customer personas
- **Gender-Specific Images**: Associate different images with personas based on gender
- **Full Block Editor Support**: Create rich, visually distinct content for each persona (planned)
- **Default Fallback**: Default content displays when persona-specific content is not available
- **Preview Mode**: Preview content as it will appear to different personas (planned)
- **Field-Level Variations**: Customize individual fields or entire content blocks (planned)

## Technical Implementation

### Content Storage

Persona content will be stored in a structured way:

1. **Current Implementation**: Uses post meta for storing basic persona data and gender-specific images
2. **Planned Enhancement**: More sophisticated storage with Redis caching for performance

### Persona Detection

The system will detect visitor personas through several methods:

1. **Cookie-Based**: Stores visitor's selected persona in a cookie
2. **URL Parameter**: Allows specifying persona via `?persona=xyz` parameter
3. **Session-Based**: Maintains persona selection across page views
4. **Default Fallback**: Uses configured default persona when none is detected

## Using the Persona System

### Creating Persona Content

1. Create a persona via the custom post type
2. Add gender-specific images
3. In future updates: Create custom content specific to that persona
4. Save the entity

### Previewing Persona Content

1. While editing content, use the "Preview as Persona" dropdown (planned)
2. Select the desired persona to preview
3. View the full rendered page as that persona would see it
4. Switch between personas to compare experiences

### Content Strategies

For effective persona-based content:

1. **Maintain Consistency**: Ensure tone and messaging are consistent within each persona
2. **Highlight Relevant Features**: Emphasize different aspects based on persona interests
3. **Vary Length and Detail**: Adjust detail level based on persona preferences
4. **Use Appropriate Imagery**: Select images that resonate with each persona

## Frontend Integration

### Displaying Persona Content

The system will handle content switching on the frontend (planned):

```php
// Example of displaying persona-aware content
$content = cme_get_persona_content($post_id, 'post', 'post_content');
echo apply_filters('the_content', $content);
```

### Custom Template Integration

For custom templates, developers will be able to use dedicated functions (planned):

```php
// Get persona-specific content for an entity
function cme_get_persona_content($entity_id, $entity_type, $content_field, $persona = null);

// Check current user's persona
function cme_get_current_persona();

// Switch content display to a specific persona
function cme_switch_persona($persona);
```

## Extending the Persona System

### Adding New Personas

To add new personas:

1. Use the WordPress admin to create new Persona posts
2. Add gender-specific images for the persona

### Custom Field Integration

To add persona support to custom fields (planned):

```php
// Register a custom field for persona content
function register_persona_custom_field($field_key, $entity_type) {
    add_action('cme_persona_editor_fields', function($entity_id, $persona) use ($field_key, $entity_type) {
        $content = cme_get_persona_content($entity_id, $entity_type, $field_key, $persona);
        // Render editor field
    }, 10, 2);
}
```

### Hooks and Filters

The persona system will provide several hooks for extension (planned):

```php
// Modify detected persona
add_filter('cme_current_persona', function($persona) {
    // Custom logic
    return $persona;
});

// Add custom persona editor tabs
add_action('cme_persona_editor_tabs', function($entity_id, $entity_type) {
    // Custom tab rendering
});

// Process persona content before display
add_filter('cme_persona_content', function($content, $entity_id, $entity_type, $field, $persona) {
    // Modify content
    return $content;
}, 10, 5);
```

## Best Practices

1. **Content First**: Create default content before persona variations
2. **Be Purposeful**: Only create variations when meaningful differences exist
3. **Test Thoroughly**: View content with each persona to ensure proper display
4. **Performance Awareness**: Use caching when implementing custom extensions
5. **Documentation**: Document custom persona implementations for team reference

## Troubleshooting

### Common Issues

1. **Content Not Switching**: Check persona detection and cookie settings
2. **Editor Not Loading**: Verify JavaScript console for errors
3. **Missing Variations**: Ensure content is saved for the specific persona
4. **Database Errors**: Check table structure and permissions

### Support

For support with the persona system:

1. Check internal documentation
2. Review source code comments
3. Contact the development team
