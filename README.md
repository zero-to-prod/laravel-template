# Laravel Template

A Laravel 12 template using feature-based modular architecture with attribute-driven API documentation and validation.

## API Controller Pattern

API controllers are single-action invokable classes decorated with `#[Endpoint]` for automatic schema generation at `/api`.

```php
#[Endpoint(
    description: 'Authenticate and receive an API token.',
    errors: [ErrorCode::invalid_credentials],
    request_schema: ApiLoginRequest::class,
    response_schema: ApiToken::class,
)]
readonly class ApiLoginController
{
    public function __invoke(): JsonResponse
    {
        // 1. Hydrate request from input
        $ApiLoginForm = ApiLoginRequest::from(request()->all());

        // 2. Validate using rules from #[Field] attributes
        $Validator = Validator::make($ApiLoginForm->toArray(), $ApiLoginForm->rules());
        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

        // 3. Business logic using column constants
        $User = User::where(User::email, $ApiLoginForm->email)->first();
        if (! $User || ! $User->matchesPassword($ApiLoginForm->password)) {
            return api_response()->unauthorized(ErrorCode::invalid_credentials);
        }

        // 4. Return response as a DataModel
        return api_response()->ok(
            ApiToken::from([
                ApiToken::token => $User->createToken($ApiLoginForm->device_name)->plainTextToken,
            ]),
        );
    }
}
```

### Controller Flow

1. **Hydrate** — `RequestClass::from(request()->all())` creates a readonly DataModel, applying `#[Describe]` casts (e.g. `sanitizeEmail`)
2. **Validate** — `Validator::make($form->toArray(), $form->rules())` uses rules extracted from `#[Field]` attributes
3. **Error response** — `api_response()->unprocessableEntity($Validator)` returns 422 with validation errors
4. **Business logic** — Query models using column constants (`User::email`, not `'email'`)
5. **Success response** — `api_response()->ok(DataModel)` returns 200 with the response schema

### `#[Endpoint]` Attribute

Decorates controllers for the self-documenting `/api` discovery endpoint:

| Parameter         | Description                                         |
|-------------------|-----------------------------------------------------|
| `description`     | Human-readable endpoint description                 |
| `errors`          | Array of `ErrorCode` cases this endpoint may return |
| `request_schema`  | DataModel class defining the request body           |
| `response_schema` | DataModel class defining the response data          |
| `accepts`         | Additional accepted content types                   |

### Request DataModels

Request classes combine `DataModel` (hydration/casting) with `HasFieldRules` (validation):

```php
readonly class ApiLoginRequest
{
    use DataModel;
    use HasFieldRules;

    /** @link $email */
    public const string email = 'email';
    #[Describe(['cast' => [self::class, 'sanitizeEmail']])]
    #[Field(description: 'User email address', rules: 'required|email')]
    public string $email;
}
```

- `#[Describe]` — Controls hydration: casting, defaults, required, nullable
- `#[Field]` — Controls API docs (`description`) and validation (`rules`)
- `rules()` — Returns `['email' => 'required|email', ...]` extracted from `#[Field]` attributes via reflection

### Response DataModels

Response classes use `DataModel` only (no validation needed):

```php
readonly class ApiToken
{
    use DataModel;

    /** @link $token */
    public const string token = 'token';
    #[Field('API authentication token')]
    public string $token;
}
```

### API Response Helper

`api_response()` returns standardized JSON via the `Api` class:

| Method                          | Status | Use case            |
|---------------------------------|--------|---------------------|
| `ok($dataModel)`               | 200    | Success             |
| `created($dataModel)`          | 201    | Resource created    |
| `unauthorized($errorCode)`     | 401    | Auth failure        |
| `notFound($errorCode)`         | 404    | Resource not found  |
| `conflict($errorCode)`         | 409    | Conflict            |
| `unprocessableEntity($validator)` | 422 | Validation failure  |

Response format:
```json
{
  "success": true,
  "message": "ApiToken",
  "type": "ApiToken",
  "data": {"token": "..."},
  "errors": []
}
```

### Error Codes

Errors use the `ErrorCode` enum. Pass cases to `#[Endpoint(errors: [...])]` and to `api_response()` methods:

```php
enum ErrorCode: string
{
    case unauthorized = 'unauthorized';
    case invalid_credentials = 'invalid_credentials';
}
```

### Column Constants

All models define column names as string constants in a `Support\*Columns` trait, enabling safe refactoring:

```php
// In the model
use UserColumns; // trait with: public const string email = 'email';

// Usage everywhere
User::where(User::email, $value)  // not User::where('email', $value)
self::email_verified_at           // in casts, fillable, hidden
```