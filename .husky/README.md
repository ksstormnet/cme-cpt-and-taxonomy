# Husky Git Hooks

This directory contains [Husky](https://typicode.github.io/husky/) Git hooks for the CME Personas project to ensure code quality.

## Available Hooks

### pre-commit

The pre-commit hook runs `lint-staged` to lint and automatically fix issues in staged files before they are committed. This helps ensure only quality code is committed.

- JavaScript/TypeScript files: ESLint with auto-fix
- CSS files: Stylelint with auto-fix
- JSON/Markdown files: Prettier formatting
- Markdown files: Markdownlint checks
- PHP files: PHPCS checks

### pre-push

The pre-push hook runs all linters to ensure the entire codebase meets standards before pushing to the remote repository.

## Troubleshooting

If you encounter issues with Husky hooks:

1. Ensure hooks are executable:

   ```bash
   chmod +x .husky/pre-commit .husky/pre-push
   ```

2. Verify Husky is properly installed:

   ```bash
   npm run prepare
   ```

3. Check for errors in the hook scripts

### Bypassing Hooks (Emergency Only)

In emergency situations, you can bypass hooks using:

```bash
git commit --no-verify
git push --no-verify
```

**Note:** This should only be done in exceptional circumstances as it bypasses quality checks.

## Adding New Hooks

To add a new Git hook:

```bash
npx husky add .husky/hook-name "npm run your-script"
```

For example, to add a commit-msg hook:

```bash
npx husky add .husky/commit-msg "npx commitlint --edit $1"
```

Remember to make the new hook executable:

```bash
chmod +x .husky/hook-name
