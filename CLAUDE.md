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

## End of Turn Instructions

1. `sail composer fix`: automated refactoring and formatting
2. `sail composer check`: validation

## MCP Servers

Servers document the `zero-to-prod/*` packages ([.mcp.json](./.mcp.json)).

They are the source of truth for how a package works: do NOT read or grep `vendor/zero-to-prod/**`, ask the server instead.

| Working on                            | Server             |
|---------------------------------------|--------------------|
| Rector rules, `rector.php`            | `laravel-rector`   |
| OpenAPI attributes, endpoint coverage | `laravel-openapi`  |
| DB enums, `Sources/Db`                | `db-model`         |
| Schema assertions                     | `schema-validator` |

`laravel-template` is this application's own server ([app/Mcp](app/Mcp), registered in [routes/ai.php](routes/ai.php)). 

Its `scaffold-endpoint` tool writes a new endpoint module: prefer it over writing the artifacts by hand.

## API endpoints

This project supports the generation of an OpenAPI document.

## Conventions

### First-Class Concepts

- Controllers
- Responses
    - Naming convention: <Concept>Response
    - Readonly DTO using [DataModel](app/Helpers/DataModel.php)