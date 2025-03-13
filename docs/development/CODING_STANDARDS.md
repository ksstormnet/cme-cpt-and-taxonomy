# Coding Standards Guide

This document outlines the coding standards and linting requirements for the CME Cruises plugin. Following these standards ensures consistency across the codebase and helps catch potential issues early through automated linting.

## Quick Reference

```bash
# Check all file types at once
npm run lint

# Fix automatically fixable issues
npm run fix

# Check/fix individual file types
npm run lint:js     # Check JavaScript files
npm run fix:js      # Fix JavaScript files
npm run lint:css    # Check CSS files
npm run fix:css     # Fix CSS files
npm run lint:md     # Check Markdown files
```

## PHP Standards

PHP code follows the WordPress Coding Standards with some customizations.

### Key PHP Rules

1. **Indentation**: Use 4 spaces (not tabs) for indentation
2. **Line Length**: Aim for 80 characters, with a hard limit of 120
3. **Naming Conventions**:
   - Classes: `CamelCase` with first letter capitalized
   - Methods/Functions: `snake_case` (lowercase with underscores)
   - Variables: `snake_case`
   - Constants: `UPPERCASE_WITH_UNDERSCORES`
4. **File Structure**:
   - One class per file, named after the class
   - Opening `<?php` tag at the beginning of the file, no closing tag
   - Namespace declaration on the first line after the opening PHP tag
5. **Documentation**:
   - All files should have a file-level docblock
   - All classes, methods, and functions should have docblocks
   - Use WordPress-style PHPDoc comments

### PHP Example

```php
<?php
/**
 * Port admin functionality.
 *
 * @package CME_Cruises
 * @subpackage Admin
 * @since 0.0.5
 */

/**
 * Handles port administration interface.
 *
 * @since 0.0.5
 */
class CME_Cruises_Port_Admin {

    /**
     * Initialize the class.
     *
     * @since 0.0.5
     * @return void
     */
    public function __construct() {
        // 4-space indentation
        add_action( 'admin_menu', array( $this, 'add_ports_menu' ) );
    }

    /**
     * Add ports admin menu.
     *
     * @since 0.0.5
     * @return void
     */
    public function add_ports_menu() {
        // Function content with proper spacing around operators
        $page_title = 'Manage Ports';
        $menu_title = 'Ports';
        $capability = 'manage_options';

        // Proper spacing in function calls
        add_menu_page(
            $page_title,
            $menu_title,
            $capability,
            'cme-ports',
            array( $this, 'display_ports_page' ),
            'dashicons-location',
            30
        );
    }
}
```

## JavaScript Standards

JavaScript code follows ESLint with WordPress configuration.

### Key JavaScript Rules

1. **Indentation**: Use tabs for indentation (WordPress standard)
2. **Line Length**: Maximum 100 characters
3. **Naming Conventions**:
   - Variables/Functions: `camelCase`
   - Constants: `UPPER_CASE`
   - Classes: `PascalCase`
4. **Modern Syntax**:
   - Use ES6+ features where appropriate
   - Prefer `const` and `let` over `var`
   - Use arrow functions for callbacks
5. **Spacing**:
   - No trailing whitespace
   - Space after keywords like `if`, `for`, `while`
   - Space around operators (`+`, `-`, `=`, etc.)

### JavaScript Example

```javascript
/**
 * Port admin functionality.
 *
 * @since 0.0.7
 */

const PORT_API_ENDPOINT = '/wp-json/cme/v1/ports';

/**
 * Initializes port admin interface.
 *
 * @since 0.0.7
 * @param {Object} options Configuration options.
 * @return {void}
 */
function initPortAdmin( options ) {
    // Tab indentation in actual code (represented with spaces in markdown)
    const { apiRoot, nonce } = options;

    // Event binding with arrow function
    document.getElementById( 'add-port' ).addEventListener( 'click', () => {
        addNewPort();
    } );

    /**
     * Adds a new port to the system.
     *
     * @since 0.0.7
     * @return {Promise} Promise resolving to the API response.
     */
    function addNewPort() {
        const portName = document.getElementById( 'port-name' ).value;
        const portCode = document.getElementById( 'port-code' ).value;

        // Proper spacing and promise usage
        return fetch( apiRoot + PORT_API_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce
            },
            body: JSON.stringify( {
                name: portName,
                code: portCode
            } )
        } )
            .then( response => response.json() )
            .then( data => {
                updatePortList( data );
                return data;
            } )
            .catch( error => {
                console.error( 'Error adding port:', error );
            } );
    }
}
```

## CSS Standards

CSS follows stylelint with WordPress configuration.

### Key CSS Rules

1. **Indentation**: Use tabs for indentation (WordPress standard)
2. **Property Order**: Alphabetical property ordering
3. **Formatting**:
   - One selector per line
   - Opening brace on the same line as the selector
   - One property declaration per line
   - Space after the colon in property declarations
   - Closing brace on a new line
4. **Units and Values**:
   - Use lowercase for hex values
   - Use shorthand hex values where possible (#fff instead of #ffffff)
   - Use relative units (em, rem, %) where appropriate

### CSS Example

```css
/* Port admin styling */
.port-list-table {
    border-collapse: collapse;
    margin: 1em 0;
    width: 100%;
}

.port-list-table th,
.port-list-table td {
    border: 1px solid #ddd;
    padding: 0.5em;
    text-align: left;
}

.port-list-table th {
    background-color: #f5f5f5;
    font-weight: 600;
}

.port-actions {
    display: flex;
    gap: 0.5em;
}

/* Responsive adjustments */
@media (max-width: 782px) {
    .port-list-table {
        font-size: 0.9em;
    }

    .port-actions {
        flex-direction: column;
    }
}
```

## Markdown Standards

Markdown files follow markdownlint standards.

### Key Markdown Rules

1. **Headers**:
   - Use ATX-style headers (`#` syntax)
   - Add a space after the `#` character
   - Headers should be surrounded by blank lines
   - Only one H1 header per document (at the top)
2. **Lists**:
   - Lists should be surrounded by blank lines
   - Consistent indentation for nested lists (2 spaces)
3. **Code Blocks**:
   - Use fenced code blocks with language specification
   - Code blocks should be surrounded by blank lines
4. **Line Length**:
   - Soft limit of 120 characters for readability
5. **Whitespace**:
   - No trailing whitespace
   - No multiple blank lines in a row

### Markdown Example

```markdown
# Port Management Guide

This document outlines the port management functionality.

## Adding a New Port

To add a new port to the system:

1. Navigate to the Ports management screen
2. Click "Add New Port" button
3. Fill in the required fields:
   - Port Name
   - Port Code
   - Country
   - Region
4. Click "Save Port"

## Port Attributes

The following attributes can be specified for each port:

| Attribute | Description | Required |
|-----------|-------------|----------|
| Name | Official port name | Yes |
| Code | 3-letter port code | Yes |
| Country | Country of the port | Yes |
| Region | Geographic region | No |

## Code Example

```php
// Example of adding a port programmatically
$port_data = array(
    'name'    => 'Nassau',
    'code'    => 'NAS',
    'country' => 'Bahamas'
);
$port_id = CME_Cruises_Port::create( $port_data );
```

## Pre-commit Hooks

When you attempt to commit your changes, the pre-commit hook will automatically:

1. Run linters on your staged files
2. Attempt to automatically fix issues in JS and CSS files
3. Report any issues that cannot be fixed automatically

If linting errors are found and cannot be fixed automatically, the commit will be rejected. You'll need to resolve these issues manually before trying to commit again.

## Troubleshooting Common Linting Errors

### PHP

- **Undefined variable/function**: Make sure all variables and functions are properly defined before use
- **Missing docblock**: Add appropriate docblocks to classes, functions, and methods
- **Naming conventions**: Follow WordPress naming conventions
- **Improper spacing**: Check spacing around operators, parentheses, and braces

### JavaScript

- **Unexpected var**: Replace `var` with `const` or `let`
- **Missing semicolon**: Add semicolons at the end of statements
- **Unused variables**: Remove or use declared variables
- **Indentation issues**: Use tabs for indentation

### CSS

- **Invalid property**: Check for typos in property names
- **Missing semicolon**: Ensure all declarations end with semicolons
- **Vendor prefixes**: Consider using autoprefixer for vendor prefixes
- **Duplicate properties**: Remove duplicate property declarations

### Markdown

- **Missing blank lines**: Add blank lines around headers and lists
- **Inconsistent list indentation**: Use consistent indentation (2 spaces) for nested lists
- **Missing language in code blocks**: Specify the language in fenced code blocks
- **Multiple consecutive blank lines**: Reduce to a single blank line
- **Hard tabs (MD010)**: Replace tab characters with spaces (typically 2 or 4 spaces)

## Manual Linting Error Resolution

When your commit is blocked by linting errors that can't be auto-fixed, follow this process to resolve them:

### Understanding Error Messages

Linting errors typically include:

1. **File path**: The file containing the error
2. **Line/column number**: Exact location of the issue
3. **Error code**: A reference code (like MD010 or ESLint rules)
4. **Description**: What's wrong with the code
5. **Context**: The problematic line or section

### Step-by-Step Resolution Process

1. **Check the error details** in the command output
2. **Open the affected file** in your editor
3. **Navigate to the specified line**
4. **Fix according to the rule description**
5. **Re-run the linter** on the file to verify the fix
6. **Stage the fixed file** with `git add`
7. **Try committing again**

### Common Manual Fixes

#### Markdown Errors

- **MD010 (Hard tabs)**:

  ```markdown
  # Before: Line with a tab character
    This line starts with a tab

  # After: Replace with spaces
      This line starts with spaces
  ```

- **MD031/MD032 (Missing blank lines)**:

  ```markdown
  # Before: No blank lines around lists
  Some text
  - Item 1
  - Item 2
  More text

  # After: Adding blank lines
  Some text

  - Item 1
  - Item 2

  More text
  ```

- **MD040 (Missing language specifier)**:

  For code blocks without a language specifier, add a language tag after the opening backticks:

  Before:
  ` ``` `
  `code here`
  ` ``` `

  After:
  ` ```javascript `
  `code here`
  ` ``` `

#### JavaScript/CSS Errors

- **Indentation**:

  ```javascript
  // Before: Inconsistent indentation
  function example() {
    const value = 1;
      if (value) {
        return true;
    }
  }

  // After: Consistent indentation
  function example() {
    const value = 1;
    if (value) {
      return true;
    }
  }
  ```

- **Trailing whitespace**:

  ```javascript
  // Before: Line with trailing spaces
  const message = "Hello";

  // After: No trailing whitespace
  const message = "Hello";
  ```

### Tips for Efficient Resolution

1. **Use an editor with linting integration** to see errors as you type
2. **Fix one type of error at a time** across all files
3. **Learn common patterns** in your linting errors to avoid them in future
4. **Create editor snippets** for complex but commonly used patterns
5. **Know your editor's search & replace tools** - they can fix many issues in batch

Remember that linting helps maintain code quality across the team. Taking the time to fix these issues prevents technical debt and improves readability for all contributors.

## Legacy Files and Exceptions

Some files in the repository are excluded from linting to accommodate legacy code that hasn't yet been updated to the current standards:

### Excluded Directories and Files

- `legacy/**/*.md` - All Markdown files in the legacy directory
- `docs/development/WORKFLOW.md` - Contains legacy code formatting examples

### How Exclusions Work

Exclusions are configured in three places:

1. `.markdownlint.json` and `.markdownlintrc` - Contains ignore patterns for markdownlint
2. `.markdownlint-cli2.yaml` - Contains glob patterns for markdownlint-cli2
3. `package.json` - Contains ignore patterns in the `lint:md` script

### Handling Legacy Content

When working with legacy content:

1. **DO NOT modify any files in the legacy directory** - This directory is kept intact for reference purposes only
2. **Don't modify formatting** of excluded files unless specifically refactoring them
3. **Don't use these files as examples** for new code - refer to this coding standards document instead
4. **Do add proper linting** when creating new files or substantially reworking old ones

### System-Specific Command Notes

When working with files and directories in this project:

1. **Use -f flag with rm and mv commands** - The system requires the force flag for deleting or moving files and directories:

   ```bash
   # Correct way to remove files
   rm -f filename.php

   # Correct way to remove directories
   rm -rf directory_name/

   # Correct way to move files with overwrite
   mv -f source_file destination_file
   ```

2. **Always check git status** before and after file operations to ensure changes are tracked properly

### Adding New Exclusions

If you need to exclude additional files from linting:

1. **Only exclude when necessary** - Prefer fixing issues over excluding files
2. **Document the reason** for the exclusion in a comment or commit message
3. **Update all config files** - Update all three config files mentioned above
4. **Consider creating a JIRA ticket** to track the technical debt for future cleanup
