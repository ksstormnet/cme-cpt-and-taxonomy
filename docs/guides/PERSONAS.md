# Persona Content System Guide

This guide provides a comprehensive overview of the CME Cruises persona content system, explaining how it works, how to use it, and how to extend it.

## What is the Persona System?

The persona system enables the creation and delivery of tailored content for different visitor types. Each visitor is identified as belonging to a specific "persona" or visitor archetype, and content is dynamically customized to match their preferences and needs.

### Supported Personas

The system supports four primary personas:

1. **Default** - Standard content shown to unidentified visitors
2. **Easy-Breezy Cruiser** - Casual cruisers looking for relaxation and simplicity
3. **Luxe Seafarer** - Luxury-focused travelers seeking premium experiences
4. **Thrill Seeker** - Adventure-oriented cruisers interested in activities and exploration

## Using Persona Shortcodes

The persona system uses shortcodes to define boundaries around content that should be displayed conditionally based on the current visitor's persona.

### Basic Shortcode Usage

The primary shortcode used for persona-specific content is `[if_persona]`:

```php
[if_persona is="easy-breezy"]
This content will only be shown to visitors with the "Easy-Breezy Cruiser" persona.
[/if_persona]
```

### Targeting Multiple Personas

You can target multiple personas by separating them with commas:

```php
[if_persona is="easy-breezy,luxe"]
This content will be shown to both "Easy-Breezy Cruiser" and "Luxe Seafarer" personas.
[/if_persona]
```

### Excluding Personas

You can also display content for everyone EXCEPT specific personas:

```php
[if_persona not="thrill"]
This content will be shown to everyone EXCEPT "Thrill Seeker" personas.
[/if_persona]
```

### Complex Content and Third-Party Shortcodes

A major advantage of the boundary-based shortcode approach is that you can include any content within the shortcode boundaries, including:

- Images and media
- Third-party shortcodes
- Complex HTML elements
- Other WordPress blocks and embeds

Example with custom HTML:

```html
[if_persona is="luxe"]
<h2>Luxury Accommodations</h2>
<p>Indulge in our premium suite options...</p>
<div class="featured-image">
  <img src="/images/luxury-suite.jpg" alt="Luxury Suite">
</div>
[/if_persona]
```

### Nesting Shortcodes

You can nest persona shortcodes for more complex conditional logic:

```html
[if_persona not="thrill"]
  <p>Relaxation is our priority...</p>
  
  [if_persona is="luxe"]
    <p>Enjoy our premium spa treatments...</p>
  [/if_persona]
[/if_persona]
```

## Previewing Persona Content

### Using Query Parameters

You can preview how content will appear to different personas by adding a `persona` query parameter to any URL:

```php
https://your-site.com/page/?persona=easy-breezy
```

This is useful for testing and verifying that persona-specific content displays correctly.

### Admin Preview

When editing content in the WordPress admin, you can use the persona selector in the sidebar to preview how your content will appear to different personas before publishing.

## Persona Switcher

You can add a persona switcher to your site that allows visitors to manually select their persona:

```php
[persona_switcher]
```

This will display a set of buttons, one for each persona. Visitors can click to change their persona, and the page will refresh to show the appropriate content.

### Customizing the Switcher

You can customize the appearance of the switcher:

```php
[persona_switcher display="dropdown" button_text="Choose Your Cruise Style"]
```

Options:

- `display`: "buttons" (default) or "dropdown"
- `button_text`: The label for the dropdown select
- `class`: Additional CSS classes for styling

## Technical Implementation

### Content Storage and Detection

Behind the scenes, the persona system:

1. Stores the visitor's selected persona in a cookie
2. Checks for the persona query parameter
3. Processes shortcodes based on the active persona
4. Shows or hides content accordingly

### Performance Considerations

The shortcode-based approach is lightweight and efficient:

- No duplicate content storage
- Minimal database queries
- Compatible with caching plugins

## Best Practices

1. **Default First**: Always consider the default experience first, then add persona-specific variations
2. **Clear Boundaries**: Keep shortcode blocks focused and well-organized
3. **Test All Personas**: Always preview your content as each persona to ensure proper display
4. **Documentation**: Document which sections of your site use persona-specific content

## Content Strategies

For effective persona-based content:

1. **Maintain Consistency**: Ensure tone and messaging are consistent within each persona
2. **Highlight Relevant Features**: Emphasize different aspects based on persona interests
3. **Vary Length and Detail**: Adjust detail level based on persona preferences
4. **Use Appropriate Imagery**: Select images that resonate with each persona

## Troubleshooting

### Common Issues

1. **Content Not Switching**: Check persona detection and cookie settings
2. **Shortcode Not Working**: Verify that shortcode syntax is correct
3. **Third-Party Compatibility**: If a third-party shortcode doesn't work inside persona shortcodes, try placing it outside and using multiple persona shortcodes around its parts

### Support

For support with the persona system:

1. Check internal documentation
2. Review source code comments
3. Contact the development team
