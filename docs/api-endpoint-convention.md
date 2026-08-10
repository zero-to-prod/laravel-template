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
- **The PHP type decides nullability, not the column.** `?string` → left out of
  `required`, emitted as `nullable: true`.
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
- Behind `auth:sanctum` the 401 is the middleware's bare `{message}`, not the envelope. Declare it inline. (Known wart; see [`agent-development-friction.md`](agent-development-friction.md) #9.)

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

## Gates

`sail composer check` — pint, rector, phpstan level 9, `openapi:validate`, 100%
coverage, `openapi:coverage`. Also:

- The document must be **expressible as Laravel rules**: no `allOf`/`oneOf`, no
  `additionalProperties: false`, no `additionalProperties` beside `properties`.
  `DocumentIsEnforceableTest` fails otherwise.
- Coverage is a hard 100%. If it drops with no failing test, look for a
  multi-line array literal on the right of `??` — xdebug blames its closing `];`.
