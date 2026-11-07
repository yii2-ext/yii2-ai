# AGENTS.md

## Project overview

`proweb/yii2-ai` is a Yii2 extension for AI/LLM access: a provider factory (Yandex AI Studio, OpenAI, Claude, Gemini,
DeepSeek, Grok, and any OpenAI-compatible endpoint) and a chat service (one-shot generation, streaming, multi-turn chat
with caller-supplied history, model listing) built on top of [ai-access](https://github.com/dg/ai-access).

- PHP 8.3+ with `ext-curl` (CI also loads `ext-fileinfo`).
- Yii `2.0.54+` or the Yii 22 development branch (`^22.0@dev`).
- PSR-4: `proweb\ai\` maps to `src/`, `proweb\ai\tests\` maps to `tests/`.

## Repository layout

- `src/` — the only code shipped in the package.
- `tests/` — PHPUnit tests; `tests/Support/` holds shared helpers (`MockHttpClient`, `RecordingHistoryProvider`,
  `YiiReset`).
- `docs/` — guides (installation, configuration, providers, usage, testing, agents). Excluded from the dist archive.
- `skills/yii2-ai/SKILL.md` — the canonical agent skill; `extra.skills.source` in `composer.json` points at `skills/`.
  `.agents/skills/yii2-ai` and `.claude/skills/yii2-ai` are symlinks to it — edit the canonical file, never a link.
- `runtime/` — local caches (PHPUnit `cacheDirectory`, PHPStan `tmpDir`). Everything inside is gitignored; never commit
  its contents.
- `.gitattributes` marks dev-only paths (`docs/`, `tests/`, `skills/`, tool configs) `export-ignore`, so the Composer
  dist archive contains only `src/`, `composer.json`, `LICENSE`, `CHANGELOG.md`, and the READMEs. If you add a file the
  extension needs at runtime, make sure it is not export-ignored.

## Setup

```bash
composer install
```

`asset-packagist.org` is already configured as a repository in `composer.json`.

## Testing

```bash
composer tests              # PHPUnit 12, config in phpunit.xml.dist
composer static             # PHPStan level max + bleeding edge + strict rules (src + tests)
composer mutation           # Infection; composer mutation-static adds PHPStan
composer check-dependencies # composer-require-checker
```

- Single test: `vendor/bin/phpunit --filter 'ChatServiceTest::testGenerate'`.
- CI enforces a 100% mutation score (`minMsi`/`minCoveredMsi` in `infection.json5`): every new branch or condition needs
  a test that kills its mutants.
- Tests mock `AIAccess\Http\Client` with `tests/Support/MockHttpClient` (record requests, queue JSON/SSE responses) and
  pass it as the `httpClient` provider option. `tests/Support/YiiReset` restores global Yii state between tests.
- `tests/bootstrap.php` defines `YII_ENABLE_ERROR_HANDLER` as `false` so exceptions propagate to the test code; keep it
  that way.

## Code style

```bash
composer ecs-check      # Easy Coding Standard (php-forge/coding-standard, PHP 8.3 preset)
composer ecs            # same, with --fix
composer rector-check   # Rector, dry run
composer rector         # Rector, apply
composer quality        # actionlint, codespell, editorconfig-checker, markdownlint, prettier, yamllint
```

- `declare(strict_types=1)` in every PHP file.
- Markdown: Prettier `printWidth` 120, `proseWrap: always`; YAML: 2-space indent.
- `composer quality` needs the CLI tools on `PATH` (actionlint, codespell, editorconfig-checker, markdownlint-cli2,
  prettier, yamllint).

## CI

All workflows delegate to `yii2-framework/actions` reusable workflows (pinned, `# v2.0.1`):

- **build** — PHPUnit on PHP 8.3/8.4/8.5 in two legs: the Yii 22 dev branch (default resolution) and Yii `2.0.54`
  (re-pinned via a composer hook). A change must pass both.
- **static** — PHPStan; **mutation** — Infection with PHPStan (Stryker dashboard); **ecs** — coding standard;
  **dependency-check** — composer-require-checker.
- **quality** and **security** (zizmor, gitleaks) run on every push/PR without path filters; the PHP workflows ignore
  `docs/`, `README*`, `CHANGELOG.md`, `.gitignore`, and `.gitattributes`.

## Conventions

- Constructor injection in services and providers; `Yii::$app` / `Yii::$container` only in controllers, views, and
  console entry points.
- `ChatService` and `ChatProvider` are `final readonly`; `ProviderFactory` is `final`. Keep them that way.
- Secrets come from the environment, never from committed config.
- Yandex: `model` is a `gpt://<folder>/<model>` URI (a bare model name plus `folder` is combined automatically); the
  auth header is `Authorization: Api-Key <key>`.
- Reasoning models may return empty text with `finish_reason: token_limit`; check `Response::getReasoning()`.

## Commits and changelog

- Commit subjects are imperative, without a Conventional Commits prefix (see `git log`).
- `CHANGELOG.md` follows Keep a Changelog; add an entry for user-visible changes.
- When the public API changes, update `skills/yii2-ai/SKILL.md` and the relevant `docs/` guide in the same change.
