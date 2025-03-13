# Git Workflow Procedures

This document outlines the standard procedures for working with Git in this project, replacing the various shell scripts and text files previously used.

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

Before committing, it's recommended to run linting with auto-fixes:

```bash
# Run all auto-fixes (JS, CSS, and Markdown)
npm run fix

# Or run individual fixes
npm run fix:js    # Fix JavaScript files
npm run fix:css   # Fix CSS files
npm run fix:md    # Fix Markdown files
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
   - Follow the [Coding Standards](CODING_STANDARDS.md) documentation

3. **Common issues and fixes**:
   - For Markdown:
     - Hard tabs (MD010): Replace tabs with spaces
     - Missing blank lines around lists and code blocks (MD031/MD032)
     - Missing language specification in code fences (MD040)
   - For JavaScript/CSS:
     - Indentation issues: Follow the project's tab/space conventions
     - Trailing whitespace: Remove extra spaces at line ends

4. **After fixing the issues**, stage your changes and try committing again:

   ```bash
   git add <fixed-files>
   git commit -m "your message"
   ```

5. **Using `--no-verify` as a last resort**:
   Use the `--no-verify` flag ONLY when:
   - You're in an emergency deployment situation
   - Making temporary WIP commits you'll clean up later
   - Issues are in legacy files that will be fixed in a separate pass

   Example:

   ```bash
   git commit -m "your message" --no-verify
   ```

   But be aware this bypasses important quality checks and should be followed up with proper fixes.

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

# Create PR using GitHub CLI (if available)
gh pr create --base dev --head feature/my-feature-name --title "My Feature Title"
```

Alternatively, create the PR through the GitHub web interface.

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

To increment patch version (e.g., 1.2.3 → 1.2.4):

```bash
# Determine current version from plugin file
CURRENT_VERSION=$(grep -o "Version: *[0-9]\+\.[0-9]\+\.[0-9]\+" "cme-cruises.php" | grep -o "[0-9]\+\.[0-9]\+\.[0-9]\+")

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
NEW_VERSION="1.3.0"
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
git commit -m "Bump version to ${NEW_VERSION}"
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
5. **Pre-Commit Checks**: Linting should pass before merging to main branches
6. **Coding Standards**: All code should follow the project's [Coding Standards](CODING_STANDARDS.md)
