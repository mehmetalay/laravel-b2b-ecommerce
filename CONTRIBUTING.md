# Contributing Guide

Thank you for your interest in contributing.

## Scope
- This repository is maintained primarily as a production B2B codebase and portfolio showcase.
- Contributions are welcome for bug fixes, documentation improvements, and maintainability enhancements.
- Large feature proposals should be discussed before implementation.

## Contribution Flow
1. Open an issue describing the problem or improvement.
2. Fork the repository and create a focused branch.
3. Keep changes small and isolated.
4. Submit a pull request with a clear summary and testing notes.

## Pull Request Checklist
- No behavioral regression in existing flows.
- Changes are limited to the intended scope.
- Code style remains consistent with the repository.
- Documentation is updated when relevant.
- New configuration or environment assumptions are documented.

## Coding Notes
- Follow `.editorconfig` and `.gitattributes`.
- Prefer explicit, readable code over clever shortcuts.
- Do not introduce secrets, credentials, or production data.
- Preserve existing Turkish text content and encoding safety.

## Commit Message Recommendation
Use concise, intent-first commit messages, for example:
- `docs: add ERP integration architecture notes`
- `fix: guard duplicate payment callback transition`
- `refactor: extract order export query builder`

## Review and Merge
- Maintainers may request revisions before merge.
- PRs that materially change business behavior may be declined unless explicitly requested.
