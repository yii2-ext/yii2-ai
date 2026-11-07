# Contributing

Thanks for your interest in contributing! Please take a moment to read this guide so your contribution goes smoothly.

## Getting started

1. Fork the repository (or clone it directly if you have write access).
2. Create a branch: `git checkout -b fix/your-change`.
3. Make your changes and commit them with a clear message.
4. Push and open a pull request against `main`.

## Pull request checklist

- [ ] The PR description explains what and why.
- [ ] `composer tests` passes (PHPUnit).
- [ ] New behavior is covered by tests; every new branch kills its mutants (CI enforces a 100% mutation score).
- [ ] `composer static` passes (PHPStan level max).
- [ ] `composer ecs-check` passes (coding standard).
- [ ] Documentation is updated if user-facing behavior changed.
- [ ] `CHANGELOG.md` has an entry for user-visible changes.
- [ ] `skills/yii2-ai/SKILL.md` is updated if the public API changed.

## Issue conventions

- Use the issue forms: bug reports, feature requests, questions.
- Label your issue with the matching `bug` / `enhancement` / `question` label and, where applicable, a `P0`–`P3`
  priority.

## Commit style

Imperative mood, no prefix: `Fix CI: distinct concurrency groups`, `Add provider presets`, `Drop template leftovers`.
Match the existing history (`git log --oneline`).

## Code style

Match the existing style of the project (linter configs are included). When in doubt, run `composer ecs-check` and
`composer rector-check` before pushing.

## Questions?

Open a `question` issue. We're friendly!
