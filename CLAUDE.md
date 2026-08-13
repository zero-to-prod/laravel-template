# CLAUDE.md

## Commands

Run inside Sail via `./vendor/bin/sail` (aliased `sail`).

```bash
sail up -d / sail down
sail composer dev                  # dev server
sail composer fix                  # refactor + format
sail composer check                # validate
sail pest --filter=UpdateUserName  # the test you are writing
sail composer openapi-validate     # document only, seconds
```

- Iterate with `pest --filter=<Test>` + `openapi-validate`. `fix` + `check` is minutes in Docker — run once, at end of turn.
- Slow commands: `cmd > /tmp/out.txt 2>&1; echo $?`, then grep the file. Piping to `tail`/`grep` drops the exit code.
- `git status --short` after `fix` separates linter edits from yours.
- **End of turn:** `sail composer fix`, then `sail composer check`.

## MCP Servers

Servers document the `zero-to-prod/*` packages ([.mcp.json](./.mcp.json)) and are
the source of truth for them. Do NOT read or grep `vendor/zero-to-prod/**` — ask
the server.

| Working on                            | Server             |
|---------------------------------------|--------------------|
| Developing this project               | `project`          |
| Rector rules, `rector.php`            | `laravel-rector`   |
| OpenAPI attributes, endpoint coverage | `laravel-openapi`  |
| DB enums, `Sources/Db`                | `db-model`         |
| Schema assertions                     | `schema-validator` |

`project` is this app's own server ([app/Mcp](app/Mcp), registered in
[routes/ai.php](routes/ai.php)). Its `scaffold-endpoint` writes a whole endpoint
module — prefer it to hand-writing.

## Architecture

Ranked; higher entries constrain lower.

### 1. Module layout — one directory per operation

`app/Modules/Api/<Concept>[/<Sub>]/<Verb>/` holds four files:
`<Concept><Verb>{Controller,Request,Response,Schema}`. Verbs: `Index`, `Show`,
`Store`, `Update`, `Destroy`; `Request` omitted when there is no body. Canonical:
[User/Update](app/Modules/Api/User/Update). Path parameters sit one level up,
beside the verbs ([Cache/KeyParameter.php](app/Modules/Api/Cache/KeyParameter.php)).
Non-API modules are `app/Modules/<Concept>/` with `Controller` + `Request` +
`Form` (+ `FormFactory`).

### 2. Column enums are the source of truth

[app/Sources/Db/App](app/Sources/Db/App): one `enum` per table, `#[Column]` per
field. Everything reads off it — `Users::name->schema()` (OpenAPI),
`->rules()` (Laravel), `->value` (column name), `Users::table()`. Never restate a
type, length or nullability. Owned by the `db-model` server.

### 3. Controller

`readonly class`, one `__invoke(Request $Request)`, no base class, no constructor
injection:

1. `#[ApiSchema(static fn () => <Concept><Verb>Schema::schema())]` on `__invoke`.
2. `$Validator = XRequest::validator($Request->all())` → `api_response()->unprocessableEntity($Validator)` on failure.
3. Do the work, keyed by column enums.
4. `api_response()->ok(...)` / `->created(...)` with an `<Concept><Verb>Response`.

`api_response()` → [Api](app/Modules/Api/Api.php): `ok`, `created`,
`unprocessableEntity`, `unauthorized`, `notFound`, `conflict`,
`unsupportedMediaType`. Errors take an
[ErrorCode](app/Modules/Api/Support/ErrorCode.php) case, never a literal.
Envelope `success`/`message`/`data`/`type` is
[ApiResponse](app/Modules/Api/Support/ApiResponse.php); `type` is the response
class basename, so tests assert `class_basename(XResponse::class)`.

### 4. Request DTO

`readonly class` + `use DataModel; use HasRequestSchema;`. Per field, a
`public const string <field> = '<field>';` directly above a
`#[Request([Request::schema => static fn () => <Table>::<col>->schema(), Request::required => true])]`
property. Reference the const, never a string literal. `schema()` builds the
OpenAPI object; `validator()` validates against it, then runs `Request::checks` —
[ValueCheck](app/Modules/Api/Support/ValueCheck.php) implementations
([Unique](app/Modules/Api/Support/Unique.php),
[Confirmed](app/Modules/Api/Support/Confirmed.php)) for what a schema cannot express.

Web side instead: `use DataModel; use IsRequest;` with `Request::rules` (plus
`messages`, `attributes`), consumed as `Validator::make(...$XRequest->validator())`
— [ProfileRequest](app/Modules/Settings/Profile/ProfileRequest.php).

### 5. Response DTO

`use DataModel; use HasResponseSchema;`, same const-above-property pairing with
`#[Response([Response::schema => ...])]`. All public properties are required;
schema falls back to the PHP type. Compose, don't restate: an index response
declares items as `XShowResponse::data()`
([CacheIndexResponse](app/Modules/Api/Cache/Index/CacheIndexResponse.php)) and
pagination as `PaginationResponse::data()`.

### 6. Schema

`implements DescribesOperation`, one `static schema()` returning
`['components' => SharedSchema::components, 'paths' => [ApiRoute::x->value => [...]]]`.
Paths key off [ApiRoute](app/Routes/ApiRoute.php), never a literal; bodies and
responses reference `XRequest::schema()` / `XResponse::schema()`. Shared refs in
[SharedSchema](app/Modules/Api/Support/SharedSchema.php): `api_error`,
`api_validation_error`, `middleware_error` (the `auth:sanctum` 401), `bearer`.
Index operations add `PaginationParameters::schema()`.

### 7. Routing

Paths are cases on [ApiRoute](app/Routes/ApiRoute.php) /
[Web](app/Routes/Web.php); route files bind `ApiRoute::x->value` to an invokable
controller and nothing else. [api.php](routes/api.php) (public),
[api_auth.php](routes/api_auth.php) (`auth:sanctum`), [web.php](routes/web.php),
[web_auth.php](routes/web_auth.php). A new endpoint = a case *and* a line.

### 8. Tests

`tests/Behavior/Api/<Concept><Verb>Test.php`, Pest `test('...')`. Wrap every
response in `$this->assertMatchesSchema(...)` — runs the body through the league
validator *and* the request-rule validator, and fails if the operation declares
no such status ([TestCase](tests/TestCase.php)). Assert with class consts
(`XRequest::name`, `ApiResponse::data`) and `Users::table()`. A declared response
no test reaches fails `openapi:coverage`.

### 9. Scaffolding

`mcp__project__scaffold-endpoint` writes all six artifacts (four files + route
case + test), leaving `@todo` where a decision is owed; `composer check` fails
until they are gone.

## Blade

Tailwind 4 + daisyUI 5 utility classes (`btn`, `card`, `input-error`, `toast`) —
no custom CSS layer.

### Pages — Folio

`resources/views/pages/**/index.blade.php`, routed by file path via Laravel Folio
([FolioServiceProvider](app/Providers/FolioServiceProvider.php)); no route-file
entry. Auth is attached there by path glob. Each page opens with a `<?php ?>`
block holding imports and `Head::title()->description()` (defaults in
[AppServiceProvider](app/Providers/AppServiceProvider.php)), then renders one
layout/card component. URLs come from [Web](app/Routes/Web.php) cases
(`Web::settingsProfile->value`), never literals.

### Two component kinds

1. **Class** — [app/View/Components](app/View/Components), PHP class and blade
   **co-located** (`Main.php` + `main.blade.php`); resolved because
   `AppServiceProvider::register()` calls
   `View::addLocation(app/View/Components)`, so `render()` returns `view('main')`
   — flat name, no `components.` prefix. Only for render-time state (auth,
   theme): `x-main` (layout) and `x-topnav`.
2. **Anonymous** — [resources/views/components](resources/views/components),
   markup only. The default; prefer it.

### Anonymous component contract

One prop — an array named after the component in camelCase — hydrated into a
DataModel, then read as typed properties:

```blade
@props(['textInput'])
@php
    use App\View\DataModels\TextInput;
    $TextInput = TextInput::from($textInput);
@endphp
```

- Backing model in [app/View/DataModels](app/View/DataModels): `use DataModel;`,
  const-above-property, `#[Describe([Describe::required => true])]` for required,
  PHP defaults for optional, `Describe::default => [self::class, 'method']` for
  computed (`TextInput::oldValue` reads `old()`).
- Callers pass const keys, never strings:
  `:svg="[Svg::name => 'x-mark', Svg::classname => 'h-4 w-4']"`.
- Components compose through projection methods on the model —
  `$TextInput->fieldset()`, `->svg()` return the child's props array, so a parent
  never restates a child's keys.
- Named slots (`<x-slot:note>`) for optional markup; `{{ $slot }}` last.

### Form fields

A `Form` DataModel declares `#[TextInput([...])]` per property and
`use HasTextInput;` ([ProfileForm](app/Modules/Settings/Profile/ProfileForm.php)).
The page reads the attribute back with `ProfileForm::textInput(ProfileForm::name)`,
spreads it, and overrides `TextInput::value` with `old(...)`. Errors render via
`TextInput::error` (defaults to the field name) + `TextInput::bag`, consumed by
`x-field`'s `@error`.

### Icons

`resources/views/svg/<name>.blade.php`, a bare `<svg>` with
`class="{{$classname}}"`. Never `@include` one directly — go through
`<x-svg :svg="[Svg::name => '...']"/>`, which includes `svg.<name>`.

### Testing

Test the **DataModel**, not rendered HTML: defaults from a partial props array,
overrides, `PropertyRequiredException` on a missing required prop, and projection
methods ([TextInputTest](tests/Feature/TextInputTest.php),
[SvgTest](tests/Feature/SvgTest.php)). Rendered pages are covered in
`tests/Behavior/Web`.

## PhpStorm MCP

Measured in this project. Always pass `projectPath`.

**Loop:** `search_file` → `read_file` (targeted range) → edit → `get_inspections`
(+ `apply_quick_fix`) → `execute_run_configuration` (one test) → `fix` + `check`
at end of turn.

### Tier 1 — reach for these

1. `get_inspections` — real IDE inspections; the only linter tool returning
   `quickFixes`. Feed its `line`/`column`/`name` to `apply_quick_fix` (one call
   per problem).
2. `execute_run_configuration(filePath, line)` — runs a single Pest test in the
   Sail container, returns exit code + output (~20s wall). Get the line from
   `get_run_configurations(filePath)`.
3. `read_file` / `list_directory_tree` — the workhorses; read only the range you need.
4. `search_file` (glob) — cheapest exact "list every X".
5. `laravel_idea_get_eloquent_model` — fields, relations, factory, migration in
   one call. `laravel_idea_get_routes` works *only* via `routeTargetPattern`
   (controller FQN).
6. `get_php_project_config` / `get_composer_dependencies` — authoritative env and
   versions (PHP 8.5, remote docker-compose interpreter).
7. `get_all_open_file_paths` — the only view of what the user is looking at.

### Tier 2 — works, budget a follow-up

8. `search_text` / `search_regex` / `search_symbol` / `skill_search` — return
   **coordinates only**, no matched text or symbol name; each hit costs a
   `read_file`. Prefer `Grep` to *see* matches.
9. `search_structural` (SSR) — exact, but emits the **whole matched element body**
   per hit (21 matches = 21 full classes). Constrain `directoryToSearch` +
   `maxResults`; start from `get_structural_patterns`.
10. `lint_files` — batch, no quick fixes, ignores the `min_severity` floor.
    `get_file_problems` is thinner still.
11. DB tools — `list_database_connections` → `list_database_schemas` →
    `get_database_object_description` is a cheap table dump,
    `execute_sql_query` needs interactive approval.
12. `git_status` — fine, though `git status --short` via Bash is cheaper.

### Tier 3 — don't bother

13. `analyze_calls` — resolves no PHP symbol (tried `A\B.m`, `A\B::m`, `\A\B::m()`,
    bare function). Non-functional here.
14. `reformat_file` / `invoke_ide_action ReformatCode` — report success, change
    nothing (formatting defers to Pint). Use `sail composer fix`.
15. `build_project` — "limited build diagnostics". No-op for PHP.
16. `laravel_idea_get_routes` by `urlPattern` — misses routes that exist.
    `laravel_idea_get_blade_component` — "Component not found" for real components.
17. `execute_terminal_command` — a second shell beside `Bash`, no added capability.
18. `execute_tool` — passthrough indirection.
19. `rename_refactoring` — works, but only verified on a 0-usage symbol.
20. `xdebug_*` needs a live session.
