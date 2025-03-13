# Security Best Practices

This document outlines security best practices for the CME Cruises plugin development.

## Directory Protection

### Index Files

All directories should contain an `index.php` file to prevent directory listings. These files should follow this template:

```php
<?php
/**
 * Silence is golden.
 *
 * This file exists to prevent directory listings.
 *
 * @package    CME_Cruises
 * @subpackage CME_Cruises/{directory_path}
 */

// Silence is golden.
```

### Creating Index Files

To add missing index.php files across the plugin, use the following bash script from the plugin's root directory:

```bash
#!/bin/bash

# Function to update an index.php file with the proper docblock
update_index_file() {
    local file_path="$1"
    local subpackage="$2"

    cat > "$file_path" << EOF
<?php
/**
 * Silence is golden.
 *
 * This file exists to prevent directory listings.
 *
 * @package    CME_Cruises
 * @subpackage CME_Cruises/$subpackage
 */

// Silence is golden.
EOF
}

# Update main index.php
update_index_file "index.php" ""

# Update admin index files
update_index_file "admin/index.php" "admin"
update_index_file "admin/css/index.php" "admin/css"
update_index_file "admin/js/index.php" "admin/js"
update_index_file "admin/partials/index.php" "admin/partials"

# Update includes index file
update_index_file "includes/index.php" "includes"

# Update public index files
update_index_file "public/index.php" "public"
update_index_file "public/css/index.php" "public/css"
update_index_file "public/js/index.php" "public/js"
update_index_file "public/partials/index.php" "public/partials"

# Update tools index file
update_index_file "tools/index.php" "tools"

# Update any custom directories
update_index_file "public/templates/index.php" "public/templates"
update_index_file "public/images/index.php" "public/images"

echo "All index.php files have been updated with proper docblocks."
```

### Checking for Missing Index Files

To check for any directories missing index.php files:

```bash
find . -type d -not -path "*/node_modules/*" -not -path "*/vendor/*" -not -path "*/.git*" | while read dir; do
    if [ ! -f "$dir/index.php" ]; then
        echo "Missing index.php in: $dir"
    fi
done
```

## Data Validation and Sanitization

### Input Validation

- Always validate user input against expected types and values
- Use WordPress functions like `sanitize_text_field()`, `absint()`, and `wp_kses_post()`
- Verify nonces with `wp_verify_nonce()` for all form submissions

### SQL Queries

- Use `$wpdb->prepare()` for all dynamic SQL queries
- Never directly interpolate variables into SQL strings
- Use placeholders (`%s`, `%d`, etc.) for all variables in queries

Example:

```php
// INCORRECT:
$results = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = '" . $post_type . "'");

// CORRECT:
$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = %s", $post_type));
```

### XSS Prevention

- Escape all dynamic data when outputting to the browser
- Use appropriate escaping functions:
  - `esc_html()` - For plain text
  - `esc_url()` - For URLs
  - `esc_attr()` - For HTML attributes
  - `esc_js()` - For inline JavaScript
  - `wp_kses()` - For allowing specific HTML tags

### File Operations

- Validate file types, sizes, and content before processing
- Use WordPress file handling functions when possible
- Never trust user-submitted filenames
- Avoid direct file system operations when WordPress alternatives exist

## Authentication and Capabilities

- Always check user capabilities before performing actions:

  ```php
  if (!current_user_can('manage_options')) {
      wp_die(__('You do not have sufficient permissions to access this page.', 'cme-cruises'));
  }
  ```

- Use proper capability checks rather than role checks
- Implement the principle of least privilege - only request capabilities you need
- For REST API endpoints, use `permission_callback` to verify access
