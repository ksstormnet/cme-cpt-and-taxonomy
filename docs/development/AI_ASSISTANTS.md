# Working with AI Assistants

This document provides guidelines for working with AI programming assistants (like Claude) on this project.

## Table of Contents

- [Working with AI Assistants](#working-with-ai-assistants)
  - [Table of Contents](#table-of-contents)
  - [Code Quality Requirements](#code-quality-requirements)
  - [Prohibited Practices](#prohibited-practices)
    - [1. Never use `--no-verify` flag](#1-never-use---no-verify-flag)
    - [2. Database Migration Restrictions](#2-database-migration-restrictions)
    - [3. Prefer deletion over commenting](#3-prefer-deletion-over-commenting)
    - [4. Never modify repository without branching](#4-never-modify-repository-without-branching)
  - [Best Practices](#best-practices)
    - [Questioning Unclear Instructions](#questioning-unclear-instructions)
    - [Suggesting Version Increments](#suggesting-version-increments)
    - [Continuous Documentation Updates](#continuous-documentation-updates)
    - [Giving Effective Instructions](#giving-effective-instructions)
    - [Common Workflows](#common-workflows)
  - [Review Process](#review-process)

## Code Quality Requirements

When using AI assistants to generate or modify code:

- All code generated by AI assistants must meet our coding standards from the start
- AI should be instructed to follow linting rules proactively rather than retroactively
- AI should favor simplicity and clarity over cleverness
- All code must be validated with linters before committing
- AI-generated code must be reviewed for performance and security implications

## Prohibited Practices

### 1. Never use `--no-verify` flag

- AI should NEVER suggest using `--no-verify` to bypass hooks
- Always resolve linting issues rather than bypassing them
- When linting fails, fix the specific issues reported
- Pre-commit hooks are essential for maintaining code quality

### 2. Database Migration Restrictions

- Never implement non-existent migrations or propose empty migration files
- Don't discuss, document or suggest potential migrations unless explicitly asked
- Never include migration-related code or comments in responses
- Avoid terms like "you might need a migration" in explanations
- Consult team lead before any migration-related implementation

### 3. Prefer deletion over commenting

- Remove deprecated code entirely instead of commenting it out
- Don't add deprecation notices unless there's a transition plan
- Follow "clean as you go" - remove obsolete code immediately
- Commit messages should document what was removed and why

### 4. Never modify repository without branching

- NEVER make changes directly to `main` or `dev` branches
- Always create a new branch for ANY change, no matter how small
- Branch names should follow conventions in [GIT_WORKFLOW.md](./GIT_WORKFLOW.md)
- All branches should be created from `dev`, not from `main`
- This applies to ALL repository content: code, documentation, configuration, etc.

## Best Practices

### Questioning Unclear Instructions

AI assistants SHOULD ask for clarification when instructions are unclear:

- **Always seek clarification** when requirements seem ambiguous or incomplete
- **Ask specific, targeted questions** rather than making assumptions
- **Provide options** when multiple approaches are possible
- **Request examples** when the expected output format is unclear

❌ **INCORRECT**: Proceeding with assumptions when instructions are unclear  
✅ **CORRECT**: "Could you clarify what you mean by X? I'm considering approaches A or B."

### Suggesting Version Increments

AI assistants should proactively suggest appropriate version increments:

- Understand semantic versioning requirements (refer to [GIT_WORKFLOW.md](./GIT_WORKFLOW.md))
- Suggest the appropriate increment level based on the nature of changes
- Provide reasoning for your suggestion
- When in doubt, recommend a higher increment level

❌ **INCORRECT**: Implementing a patch version when adding new features  
✅ **CORRECT**: "Since this adds new functionality while maintaining backward compatibility, I recommend incrementing the MINOR version (0.x.0)."

### Continuous Documentation Updates

AI assistants should prioritize documentation updates:

- Update documentation alongside code changes, not afterwards
- Keep README files, user guides, and technical documentation in sync
- Update checklists and project status documents as tasks are completed
- Document any API changes, new features, or behavioral changes

❌ **INCORRECT**: "We'll update the documentation after implementing all changes"  
✅ **CORRECT**: "I've updated both the implementation and its documentation to keep them in sync"

### Giving Effective Instructions

When working with AI assistants:

1. **Be specific about standards**: Reference the coding standards document
2. **Provide project context**: Help the AI understand the project architecture
3. **Review outputs carefully**: Don't assume code is correct just because it looks good
4. **Provide feedback**: Tell the AI what it did well and what needs improvement

### Common Workflows

**Good Example**: Asking for help with specific tasks

```text
"Please help me implement a function that validates persona settings according to
our coding standards. Here's an example of similar functionality: [code snippet]"
```

**Bad Example**: Vague requests

```text
"Fix this code for me" (without specifying standards or what "fix" means)
```

## Review Process

All AI-generated code should go through the same review process as human-written code:

1. Verify it meets all coding standards
2. Run linters and fix any issues
3. Test the functionality thoroughly
4. Review for security implications
5. Check for performance considerations
6. Ensure proper documentation

By treating AI as a tool rather than a replacement for good development practices,
we can maintain code quality while benefiting from AI assistance.
