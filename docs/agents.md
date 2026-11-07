# AI assistance

The editable source is [`skills/yii2-ai/SKILL.md`](../skills/yii2-ai/SKILL.md) in this package. It helps an agent set up
AI/LLM providers (Yandex AI Studio, OpenAI, Claude, Gemini, DeepSeek, Grok, and OpenAI-compatible endpoints), use the
chat service, wire the console commands, and test against a mock HTTP client. Guides stay in `docs/`; API contracts stay
in PHPDoc. Edit the package sources, not generated copies.

## Install in an application

### Automatic Composer installation

Complete the
[one-time application setup](https://yii2-framework.github.io/docs/project/ai-agents/#composer-installation) to enable
`llm/skills` and set `auto-sync` to `true` in `skills.json`. Approve `proweb/yii2-ai` explicitly in
`dependencies.composer.trusted`. The installed package must include its skill bundle; its `extra.skills.source` declares
the directory.

```bash
composer install
composer skills:show
```

With `auto-sync` enabled, later dependency installs and updates synchronize the skill automatically; no per-skill
installer is required. If `auto-sync` is `false`, run `composer skills:update` after dependency installation or updates.

### Optional npx installation

Run the pinned installer from the application's root, selecting the clients you use:

```bash
npx skills@1.7.2 add /absolute/path/to/yii2-ai --list
npx skills@1.7.2 add /absolute/path/to/yii2-ai \
  --skill yii2-ai --agent claude-code codex opencode
```

The source can be a package checkout or `./vendor/proweb/yii2-ai` when that installed release includes the skill. Once
the package is published on Packagist, pass the composer name `proweb/yii2-ai` instead of a path.

Use `--list` to inspect the source without installing. Keep project scope; omit `--global` for an application-specific
installation. The installer uses links by default; `--copy` creates an installation snapshot that must be refreshed when
upgrading. Installed copies are outputs, not another editable source.

Installing the Composer package alone does not expose skills in `vendor/` to an agent opened at the application root.
Install the skill explicitly.

## Paths and activation

| Client                                                | Project path                                              | Explicit activation                             |
| ----------------------------------------------------- | --------------------------------------------------------- | ----------------------------------------------- |
| [Claude Code](https://code.claude.com/docs/en/skills) | `.claude/skills/yii2-ai/SKILL.md`                         | `/yii2-ai`                                      |
| [Codex](https://learn.chatgpt.com/docs/build-skills)  | `.agents/skills/yii2-ai/SKILL.md`                         | `$yii2-ai` or the skill picker                  |
| [OpenCode](https://opencode.ai/docs/skills/)          | `.opencode/skills/yii2-ai/SKILL.md`, or `.agents/skills/` | Ask it to load `yii2-ai` through its skill tool |

The verified `skills` 1.7.2 installer uses `.agents/skills/` for Codex and OpenCode, and `.claude/skills/` for Claude.
For manual installation, copy or link the complete skill directory, retaining `<name>/SKILL.md`. Avoid separately edited
copies or multiple installations of the same name within a client's search paths.

This source checkout exposes links in `.agents/skills/` and `.claude/skills/` to the canonical `skills/` directory. No
per-client version of the instructions is maintained. When using OpenCode, verify how the installed version handles the
two aliases; the installer needs only its `.agents/skills/` installation.

## Verify each stage

1. **Installation:** the directory and frontmatter exist in the chosen client path.
2. **Discovery:** open a fresh client session at the application root and check its skill list or picker.
3. **Activation:** invoke the skill and verify the client loaded its body. Automatic selection is model-dependent;
   reading `SKILL.md` manually is separate evidence.
4. **Selective consultation:** use the relevant local guide from `docs/` rather than loading the entire documentation
   index.
5. **Functional result:** run a chat request through the console command or the chat service and check the provider
   response, streaming, and history handling.

`--list` verifies the installer's discovery only; it does not run Claude, Codex, or OpenCode. Application instructions
and permissions still apply when a skill is activated.

[Back to documentation](../README.md#documentation)
