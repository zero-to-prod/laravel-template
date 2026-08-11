# CLAUDE.md

## Commands

These commands run inside the Sail container via `./vendor/bin/sail` (aliased as `sail`).

```bash
sail up -d
sail down
sail composer dev # start development server
sail composer fix # automated refactoring
sail composer check # validation
sail pest --filter=UpdateUserName # the tests you are writing
sail composer openapi-validate # the document, seconds, no suite
```

## Running Commands

- Iterate with `sail pest --filter=<Test>` and `sail composer openapi-validate`. The full `fix` + `check` pair is minutes in Docker: run it once, at end of turn.
- Slow commands: `cmd > /tmp/out.txt 2>&1; echo $?`, then grep the file. Piping to `tail`/`grep` drops the exit code and hides failures that scrolled past.
- `git status --short` after `fix`: separates linter and concurrent edits from your own.

## End of Turn Instructions

1. `sail composer fix`: automated refactoring and formatting
2. `sail composer check`: validation

## MCP Servers

Servers document the `zero-to-prod/*` packages ([.mcp.json](./.mcp.json)).

They are the source of truth for how a package works: do NOT read or grep `vendor/zero-to-prod/**`, ask the server instead.

| Working on                            | Server             |
|---------------------------------------|--------------------|
| Developing this project               | `project`          |
| Rector rules, `rector.php`            | `laravel-rector`   |
| OpenAPI attributes, endpoint coverage | `laravel-openapi`  |
| DB enums, `Sources/Db`                | `db-model`         |
| Schema assertions                     | `schema-validator` |

`project` is this application's own server ([app/Mcp](app/Mcp), registered in [routes/ai.php](routes/ai.php)).

Its `scaffold-endpoint` tool writes a new endpoint module: prefer it over writing the artifacts by hand.

## API endpoints

This project supports the generation of an OpenAPI document.

## Conventions

### First-Class Concepts

- `Controller`
- `Request`
    - Naming convention: <Concept>Request
    - Readonly DTO using [HasRequestSchema](app/Modules/Api/Support/HasRequestSchema.php)
- `Response`
    - Naming convention: <Concept>Response
    - Readonly DTO using [DataModel](app/Helpers/DataModel.php)
- `Schema`
    - Naming convention: <Concept>Schema
    - Implement [DescribesOperation](app/Modules/Api/Support/DescribesOperation.php)