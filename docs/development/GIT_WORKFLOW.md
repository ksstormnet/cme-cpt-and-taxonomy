# Git Workflow Procedures

This document outlines the standard procedures for working with Git in the CME Personas plugin project.

## Regular Commit Process

### 1. Development on Feature Branches

```bash
# Make sure you're on dev and it's up to date
git checkout dev
git pull origin dev

# Create new feature branch
git checkout -b feature/my-feature-name
```

### 2. Make and Commit Changes

#### Linting and Auto-Fixes

Before committing, you should run linting with auto-fixes:

```bash
# Run all linters
npm run lint

# Or run individual linters
npm run lint:js    # Check JavaScript files
npm run lint:css   # Check CSS files
npm run lint:php   # Check PHP files
npm run lint:md    # Check Markdown files
```

For fixing automatically fixable issues:

```bash
# Attempt to fix ESLint issues
npx eslint --fix path/to/file.js

# Attempt to fix Stylelint issues
npx stylelint --fix path/to/file.css

# Attempt to fix Markdown issues
npx markdownlint-cli2-fix path/to/file.md
```

#### Staging and Committing

```bash
# Stage files (either all or selectively)
git add .                  # All files
# OR
git add path/to/files      # Selective files

# Commit changes with descriptive message
git commit -m "feat: description of your changes"
```

> **Note:** The pre-commit hook will automatically run linting on staged files and attempt to fix issues when possible. If issues can't be fixed automatically, the commit will be aborted with error messages indicating what needs to be fixed manually.

#### Handling Linting Errors

When the pre-commit hook catches linting errors, follow these steps:

1. **Review the error messages** in the terminal which describe:
   - The file with the issue
   - The line number and column
   - A description of the problem
   - Often a reference to the specific rule being violated

2. **Fix the issues manually** by:
   - Opening the referenced files
   - Navigating to the specified line numbers
   - Making the necessary corrections based on the rule description
   - Following the [Coding Standards](CODING_STANDARDS.md) documentation

3. **After fixing the issues**, stage your changes and try committing again:

   ```bash
   git add <fixed-files>
   git commit -m "your message"
   ```

4. **Important:** Never use `--no-verify` to bypass pre-commit hooks unless explicitly instructed by a senior developer or team lead.

Use conventional commit message format:

- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting, etc.)
- `refactor:` - Code change that neither fixes a bug nor adds a feature
- `perf:` - Performance improvements
- `test:` - Adding or correcting tests
- `chore:` - Changes to the build process or auxiliary tools

### 3. Create Pull Request

```bash
# Push the branch to remote
git push -u origin feature/my-feature-name

# Create PR using GitHub CLI
gh pr create --base dev --head feature/my-feature-name --title "My Feature Title" --body "Description of your changes"
```

Your PR should include:
- A clear title describing the change
- A detailed description of what was changed and why
- Any relevant issue numbers (e.g., "Fixes #123")
- Screenshots or examples if applicable

### 4. After PR is Merged

```bash
# Switch back to dev
git checkout dev

# Update local dev branch
git pull origin dev

# Clean up local feature branch
git branch -d feature/my-feature-name
```

## Version Increment Process

Follow these steps to update version numbers and create a release:

### 1. Ensure Clean Working Directory

```bash
# Check for uncommitted changes
git status

# Make sure you're on the main branch
git checkout main
git pull origin main
```

Ensure there are no uncommitted changes before proceeding.

### 2. Update Version Numbers

#### For Simple Patch Version Increment

To increment patch version (e.g., 1.0.0 → 1.0.1):

```bash
# Determine current version from plugin file
CURRENT_VERSION=$(grep -o "Version: *[0-9]\+\.[0-9]\+\.[0-9]\+" "cme-personas.php" | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+")

# Calculate new version
IFS='.' read -r -a version_parts <<< "${CURRENT_VERSION}"
NEW_MAJOR="${version_parts[0]}"
NEW_MINOR="${version_parts[1]}"
NEW_PATCH=$((version_parts[2] + 1))
NEW_VERSION="${NEW_MAJOR}.${NEW_MINOR}.${NEW_PATCH}"

echo "Current version: ${CURRENT_VERSION}"
echo "New version: ${NEW_VERSION}"
```

#### For Custom Version Set

For manual version changes (e.g., for minor or major releases):

```bash
# Set version manually (replace with desired version)
NEW_VERSION="1.1.0"
```

### 3. Update Files With New Version

Find and update version numbers in all relevant files:

```bash
# Find files with version patterns
find . -type f \( -name "*.php" -o -name "*.css" -o -name "*.js" -o -name "*.json" -o -name "*.txt" -o -name "*.md" \) -not -path "*/node_modules/*" -not -path "*/vendor/*" -not -path "*/.git/*" -exec grep -l "[0-9]\+\.[0-9]\+\.[0-9]\+" {} \;
```

Then update version numbers in:

- Plugin header: `* Version: x.y.z`
- Constants: `const VERSION = 'x.y.z'`
- Define statements: `define('PLUGIN_VERSION', 'x.y.z')`
- Variable assignments: `$version = 'x.y.z'`
- README.md: `**Version:** x.y.z`
- Composer.json: `"version": "x.y.z"`

### 4. Commit Version Changes

```bash
# Commit version changes
git add -A
git commit -m "chore: bump version to ${NEW_VERSION}"
```

### 5. Create Release Tags

```bash
# Create tags both with and without v prefix
git tag -a "${NEW_VERSION}" -m "Release ${NEW_VERSION}"
git tag -a "v${NEW_VERSION}" -m "Release ${NEW_VERSION}"
```

### 6. Push Changes to Remote

```bash
# Push commits and tags
git push origin main
git push origin "${NEW_VERSION}" "v${NEW_VERSION}"
```

### 7. Check Deployment Status

After pushing the tags, check GitHub Actions to ensure the deployment workflow was triggered:

```bash
# Repo URL for actions
GITHUB_REPO_URL=$(git config --get remote.origin.url | sed 's/git@github.com:/https:\/\/github.com\//' | sed 's/\.git$//')
echo "Check deployment status at: ${GITHUB_REPO_URL}/actions"
```

## Important Rules

1. **Version Format**: Always use semantic versioning (x.y.z)
2. **Clean Directory**: Always ensure clean working directory before releases
3. **Tag Uniqueness**: Verify tags don't already exist before creating
4. **Branch Structure**:
   - `main`: Production-ready code
   - `dev`: Primary development branch
   - `feature/*`: Feature development
   - `bugfix/*`: Bug fixes
   - `chore/*`: Maintenance tasks
   - `docs/*`: Documentation updates
5. **Pre-Commit Checks**: Linting must pass before committing
6. **Coding Standards**: All code should follow the project's [Coding Standards](CODING_STANDARDS.md)
7. **Never Bypass Hooks**: Avoid using `--no-verify` to bypass pre-commit hooks
8. **Make Frequent Commits**: Make small, focused commits rather than large infrequent ones
