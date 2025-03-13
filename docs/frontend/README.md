# Frontend Integration Guide

This guide explains how to integrate persona-specific content into your theme and frontend experience.

## Shortcodes

The plugin provides several shortcodes for displaying persona-specific content and adding persona switchers to your site.

### Persona Content Shortcode

Use the `[persona_content]` shortcode to display content specific to a persona:

```text
[persona_content persona="business" entity_id="123" field="content"]
  Default content displayed for other personas
[/persona_content]
```

**Parameters:**

- `persona` - (Optional) The persona ID to display content for. If not specified, will use the current active persona.
- `entity_id` - (Optional) The post/entity ID to get content from. Defaults to the current post.
- `entity_type` - (Optional) The entity type. Defaults to 'post'.
- `field` - (Optional) The field to display ('title', 'content', 'excerpt'). Defaults to 'content'.

If persona-specific content is available, it will be displayed. Otherwise, the default content (if provided) will be shown.

### Conditional Persona Content Shortcode

Use the `[if_persona]` shortcode to conditionally display content based on the active persona:

```text
[if_persona is="business"]
  Content only shown for business persona
[/if_persona]

[if_persona is="family,luxury"]
  Content shown for family or luxury personas
[/if_persona]

[if_persona not="business"]
  Content shown for all personas except business
[/if_persona]
```

**Parameters:**

- `is` - Comma-separated list of personas for which to show the content
- `not` - Comma-separated list of personas for which to hide the content

You can use either parameter, but not both at the same time.

### Persona Switcher Shortcode

Use the `[persona_switcher]` shortcode to add a persona switcher to your site:

```text
[persona_switcher]
```

Or with custom options:

```text
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
$success = cme_set_persona('business', true);
```

Sets the active persona.

- First parameter: The persona ID to set
- Second parameter: Whether to set a cookie (defaults to true)
- Returns: Boolean indicating success

### Get Persona Content

```php
$content = cme_get_persona_content($post_id, 'post', 'content', $persona_id);
```

Gets persona-specific content for an entity.

- First parameter: The entity ID (e.g., post ID)
- Second parameter: The entity type (default: 'post')
- Third parameter: The content field name (default: 'content')
- Fourth parameter: The persona ID (null for current)
- Returns: The persona-specific content, or original content if not found

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

### Displaying Persona-Specific Content in a Template

```php
// In your theme's template file
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
 <header class="entry-header">
  <h1 class="entry-title">
   <?php echo cme_get_persona_content(get_the_ID(), 'post', 'title'); ?>
  </h1>
 </header>

 <div class="entry-content">
  <?php echo cme_get_persona_content(get_the_ID(), 'post', 'content'); ?>
 </div>
</article>
```

### Using Conditional Persona Content in a Template

```php
<?php
// Check if the current persona is 'business'
if (cme_get_current_persona() === 'business') :
?>
  <div class="business-specific-notice">
    Special offer for business travelers!
  </div>
<?php endif; ?>
```

### Dynamic Content Refresh

To enable dynamic content refreshing without page reload when a persona is switched:

1. Add the `cme-persona-dynamic` class to elements with persona-specific content
2. Add data attributes for the post ID and field

```html
<div class="cme-persona-dynamic" data-post-id="123" data-field="content">
 <!-- Content will be dynamically updated when persona changes -->
 <?php echo cme_get_persona_content(123, 'post', 'content'); ?>
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

For persona-specific content:

```css
.cme-personalized-content {
 /* Styles for persona-specific content */
}
.cme-personalized-content-tag {
 /* Styles for the persona tag */
}
```
