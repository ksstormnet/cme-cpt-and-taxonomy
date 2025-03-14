# Frontend Integration Guide

This guide explains how to integrate persona-specific content into your theme and frontend experience.

## Shortcodes

The plugin provides several shortcodes for displaying persona-specific content and adding persona switchers to your site.

### Conditional Persona Content Shortcode

Use the `[if_persona]` shortcode to conditionally display content based on the active persona:

```php
[if_persona is="easy-breezy"]
  Content only shown for easy-breezy persona
[/if_persona]

[if_persona is="luxe,thrill"]
  Content shown for luxe or thrill personas
[/if_persona]

[if_persona not="thrill"]
  Content shown for all personas except thrill
[/if_persona]
```

**Parameters:**

- `is` - Comma-separated list of personas for which to show the content
- `not` - Comma-separated list of personas for which to hide the content

You can use either parameter, but not both at the same time.

### Persona Switcher Shortcode

Use the `[persona_switcher]` shortcode to add a persona switcher to your site:

```php
[persona_switcher]
```

Or with custom options:

```php
[persona_switcher display="dropdown" button_text="Choose Your Experience" class="my-custom-class"]
```

**Parameters:**

- `display` - (Optional) The display type: 'buttons' (default) or 'dropdown'
- `button_text` - (Optional) Text to display for the dropdown label
- `class` - (Optional) Additional CSS classes to add to the switcher

## Template Functions

The plugin also provides template functions that can be used in your theme files.

### Get Current Persona

```php
$current_persona = cme_get_current_persona();
```

Returns the ID of the currently active persona.

### Set Persona

```php
$success = cme_set_persona('easy-breezy', true);
```

Sets the active persona.

- First parameter: The persona ID to set
- Second parameter: Whether to set a cookie (defaults to true)
- Returns: Boolean indicating success

### Get All Personas

```php
$personas = cme_get_all_personas();
```

Returns an array of all available personas in the format `[id => name]`.

## Examples

### Adding a Persona Switcher to the Header

```php
// In your theme's header.php
<div class="site-header-persona-switcher">
  <?php echo do_shortcode('[persona_switcher display="dropdown" button_text="Select Your Experience"]'); ?>
</div>
```

### Using Conditional Persona Content in a Template

```php
<?php
// Check if the current persona is 'luxe'
if (cme_get_current_persona() === 'luxe') :
?>
  <div class="luxe-specific-notice">
    Special offer for luxury travelers!
  </div>
<?php endif; ?>
```

### Using Shortcodes in Templates

```php
<div class="trip-highlights">
  <h2>Highlights of Your Trip</h2>
  
  <?php echo do_shortcode('[if_persona is="easy-breezy"]
    <p>Relaxing beaches and no-hassle excursions await!</p>
    <img src="/images/easy-cruise.jpg" alt="Relaxing cruise experience">
  [/if_persona]'); ?>
  
  <?php echo do_shortcode('[if_persona is="thrill"]
    <p>Get ready for adventure with our extreme excursions!</p>
    <img src="/images/thrill-cruise.jpg" alt="Adventure cruise experience">
  [/if_persona]'); ?>
</div>
```

## Styling

The plugin includes CSS classes for styling the persona switcher and persona-specific content. You can extend or override these styles in your theme's CSS.

For the persona switcher:

```css
.cme-persona-switcher {
 /* Styles for the switcher container */
}
.cme-persona-buttons {
 /* Styles for the buttons container */
}
.cme-persona-button {
 /* Styles for individual buttons */
}
.cme-persona-button.active {
 /* Styles for the active button */
}
.cme-persona-select {
 /* Styles for the dropdown selector */
}
```

## Best Practices

1. **Use boundary-based shortcodes**: The `[if_persona]` shortcode is the primary method for persona content customization. It provides clear boundaries around content and maintains compatibility with other WordPress functionality.

2. **Nest shortcodes for complex scenarios**: You can nest `[if_persona]` shortcodes for more complex conditional logic:

```html
[if_persona not="thrill"]
  <div class="relaxation-options">
    <h2>Relaxation Options</h2>
    
    [if_persona is="luxe"]
      <div class="premium-options">
        <h3>Premium Relaxation</h3>
        <!-- Premium content here -->
      </div>
    [/if_persona]
    
    <div class="standard-options">
      <!-- Standard content here -->
    </div>
  </div>
[/if_persona]
```

3. **Default content first**: Always design your pages with default content first, then add persona-specific variations. This ensures a good experience for all users, even those who don't have a persona assigned.

4. **Add persona switchers in accessible locations**: Provide users with easy ways to change their persona in common areas like header, footer, or sidebar.
