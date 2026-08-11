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

[.mcp.json](./.mcp.json)

## API endpoints

Building or changing one: follow `docs/api-endpoint-convention.md` — the 6-artifact module shape (route case → request DTO → response DTO → schema → controller → test). Read it instead of copying an existing module.

## Conventions

### First-Class Concepts

- Controllers
  - Naming convention: <Concept>Controller
  - Use `readonly` invokable classes
- Responses
  - Naming convention: <Concept>Response
  - Readonly DTO using [DataModel](app/Helpers/DataModel.php)