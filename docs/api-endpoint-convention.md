# How this app builds an API endpoint

The spec the code follows. Read this instead of copying `Authenticated`/`Logout`.
Working reference: `app/Modules/Api/User` (no body) and `app/Modules/Api/Login`
(with body).

## The artifacts

One module per endpoint, `app/Modules/Api/<Name>/`. Six files, in this order:

| # | Artifact | Where |
|---|---|---|
| 1 | Route case | `app/Routes/ApiRoute.php` — `case user = self::prefix.'/user';` |
| 2 | Request DTO — **only if it takes a body** | `<Name>Request.php` |
| 3 | Response DTO | `<Name>Response.php` |
| 4 | Schema | `<Name>Schema.php` |
| 5 | Controller | `<Name>Controller.php` |
| 6 | Test | `tests/Behavior/Api/<Name>Test.php` |

Registration: `routes/api.php` for public, `routes/api_auth.php` for
`auth:sanctum`. Both are grouped in `bootstrap/app.php`; `routes/api.php` does
not mention its sibling.

## 1. Route case

```php
case user = self::prefix.'/user';
```

Never a literal path anywhere else — the schema, the route file and the tests all
read `ApiRoute::user->value`.

**A case is per path, not per endpoint.** The case *value* is the path, so two
methods on one path share one case: adding `PATCH /api/user` alongside
`GET /api/user` adds a route, a module and a schema, but no case. Artifact 1 of
the six is the one that is sometimes already done.

Those modules stay separate all the way down — `User` and `UpdateUserName` each
key `ApiRoute::user->value` in their own `<Name>Schema`, one under `get` and one
under `patch`. The generator merges path fragments with
`array_replace_recursive`, so the two operations land side by side and neither
file has to know about the other. Two modules declaring the *same* method on the
same path is the collision that silently loses one; nothing checks for it.

## 2. Request DTO (bodies only)

`use DataModel; use HasRequestSchema;` — one `public const string` plus one typed
property per field, each carrying `#[Request]`.

```php
public const string email = 'email';

#[Request([
    Request::schema => static function (): array {
        return [...Users::email->schema(), Property::format => Property::email, Property::description => 'User email'];
    },
    Request::required => true,
])]
public string $email;
```

Spread the column schema; add only what the column cannot know. `Request::checks`
takes `ValueCheck`s for anything rules cannot express. `validator($data)` returns
an ordinary Laravel validator.

## 3. Response DTO

`use DataModel; use HasResponseSchema;` — same const-plus-property shape, each
carrying `#[Response]`.

```php
public const string email_verified_at = 'email_verified_at';

#[Response([Response::schema => static function () {
    return Users::email_verified_at->schema();
}])]
public ?string $email_verified_at;
```

- `Response::schema` adopts the column's schema — `format`, `maxLength`,
  `description`, all of it. Use it whenever a column backs the field.
- `Response::description` alone when no column does (see
  `ApiLoginResponse::$token`: the column describes the stored hash, the response
  carries the plain text token).
- **The PHP type decides nullability, not the column.** `?string` → emitted as
  `nullable: true`. Every declared field is `required` either way: nullable
  means the *value* may be null, never that the *key* may be missing. As
  Laravel rules that is `present` plus `nullable`.
- **A model with any nullable property needs `#[Describe([Describe::nullable =>
  true])]` on the class.** `from()` reaches a property through `isset()`, which
  cannot tell an absent key from a null one and initializes neither, so without
  it the field is dropped from the body instead of published as null.
  `ResponseNullabilityTest` fails when it is missing — it is the one rule here
  that no amount of schema validation can catch on its own.
- No properties at all → no `data` key in the schema, because `Api::respond()`
  strips the empty array.
- The envelope's `type` is `class_basename()` of this class. Renaming the class
  changes the wire contract.

## 4. Schema

`implements DescribesOperation`, one `static schema()` returning
`['components' => SharedSchema::components, 'paths' => [...]]`.

- Key the path by `ApiRoute::<case>->value`.
- `operationId`, `summary`, `tags`, and `security => [[SharedSchema::bearer => []]]` when authenticated.
- Declare **every** status the method can return. One omitted is a test failure the first time it is reached.
- 200 → `<Name>Response::schema()`. Errors → `$ref: SharedSchema::api_error`, or `api_validation_error` for 422.
- Behind `auth:sanctum` the 401 is the middleware's bare `{message}`, not the envelope. `$ref: SharedSchema::middleware_error`, described by `SharedSchema::middleware_error_description`. (Why it is not the envelope: [`agent-development-friction.md`](agent-development-friction.md) #5.)

## 5. Controller

`readonly class`, invokable, attribute wrapping the schema call:

```php
#[ApiSchema(static function (): array {
    return ApiUserSchema::schema();
})]
public function __invoke(Request $Request): JsonResponse
{
    return api_response()->ok(ApiUserResponse::from(User::authenticated($Request)->toArray()));
}
```

- `api_response()` only: `ok`, `created`, `unauthorized`, `notFound`, `conflict`, `unprocessableEntity`, `unsupportedMediaType`.
- Every error message comes from `ErrorCode`.
- Hydrate from `$Model->toArray()`, which honours `$hidden` — do not hand-list fields you want to keep secret.
- Object variables are PascalCase (`$User`, `$Request`, `$Validator`).

## 6. Test

`tests/Behavior/Api/<Name>Test.php`. **One test per declared response** — that is
what `openapi:coverage` counts.

```php
$this->assertMatchesSchema($this->withToken($token)->getJson(ApiRoute::user->value))
    ->assertOk()
    ->assertJsonPath('data.id', $User->id);
```

- `assertMatchesSchema()` resolves the operation from the request, so never name the path, method or status.
- It runs **both** validators over the body (see [`prompts/cross-validator-parity.md`](prompts/cross-validator-parity.md)). Keep the ordinary value assertions: it proves shape, not correctness.
- A declared `security` requirement needs `->withToken('any-value')`, or the 401 can never be exercised either. `Sanctum::actingAs()` sets no header.
- Derive expected `type`/`message` from the DTO (`class_basename(ApiUserResponse::class)`), never a literal.
- For serialized dates, expect `$Model->toArray()[...]` — `Model::serializeDate()` output, not `toIso8601String()`.
- Table names come from the enum too: `assertDatabaseHas(Users::table(), [...])`.

### Covering the 422

`assertMatchesSchema()` validates the request first, so a body the document
rejects never reaches the 422 — the assertion fails describing the request. The
one invalid body the document still admits is **a blank required string**: a
required non-nullable string becomes Laravel's `required`, which refuses `''`,
while league reads it as a perfectly good `string`. That gap is what makes the
status reachable at all, and it is the default recipe for the test:

```php
$this->assertMatchesSchema(
    $this->withToken($token)->patchJson(ApiRoute::user->value, [UpdateUserNameRequest::name => ''])
)->assertStatus(422)->assertJsonValidationErrors(UpdateUserNameRequest::name);
```

Every other 422 — a missing field, a wrong type, an over-long value — needs an
ordinary test with no `assertMatchesSchema()`. Write those too; they just do not
count toward `openapi:coverage`. `ApiLoginTest` has one of each.

## Gates

`sail composer check` — pint, rector, phpstan level 9, `openapi:validate`, 100%
coverage, `openapi:coverage`. Also:

- The document must be **expressible as Laravel rules**: no `allOf`/`oneOf`, no
  `additionalProperties: false`, no `additionalProperties` beside `properties`.
  `DocumentIsEnforceableTest` fails otherwise.
- Coverage is a hard 100%. If it drops with no failing test, look for a
  multi-line array literal on the right of `??` — xdebug blames its closing `];`.
