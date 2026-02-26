# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

All commands run inside the Sail container via `./vendor/bin/sail` (aliased below as `sail`).

```bash
# Start/stop the environment
sail up -d                                    # start containers
sail down                                     # stop containers

# Development (runs server, queue, logs, and vite concurrently)
sail composer dev

# Tests
sail test                                     # all suites
sail test --testsuite=Behavior                # behavior tests only
sail test --testsuite=Unit                    # unit tests only
sail test --filter=ApiLoginTest               # single test class
sail test --filter=test_method                # single test method

# Code formatting
sail pint                                     # fix all files
sail pint --test                              # check without fixing

# Artisan & Composer
sail artisan <command>
sail composer <command>

# IDE helpers (PHPStorm autocomplete)
sail composer ide
```

## Architecture

This is a Laravel 12 template (PHP 8.4+) using a **feature-based modular architecture** with attribute-driven API documentation and validation.

### Module System (`app/Modules/`)

Each feature is a self-contained module with its own controller, request objects, views, and tests. Modules are organized by domain:
- `Api/Login`, `Api/Logout`, `Api/Authenticated` — API endpoints
- `Api/Discovery` — Self-documenting endpoint at `/api` that introspects all `#[Endpoint]` attributes via reflection
- `Register`, `Login`, `Logout` — Web features

### Attribute-Based API Pattern

Controllers are decorated with `#[Endpoint]` for automatic API schema generation. Request/form objects use `#[Field]` attributes for validation rules and documentation:

```php
#[Endpoint(description: '...', errors: [ErrorCode::invalid_credentials], response_schema: ApiToken::class)]
readonly class ApiLoginController { ... }

readonly class ApiLoginRequest {
    use DataModel, HasFieldRules;
    #[Field(description: '...', rules: 'required|email')]
    public string $email;
}
```

### Route Singletons (`app/Routes/`)

Routes are defined as singleton classes with string constants, accessed via helpers: `api()->login`, `web()->register`. URL templates use `{{key}}` placeholders resolved by `render_url()`.

### Key Helpers (`app/Helpers/`)

- `DataModel` trait — Wraps `zero-to-prod/data-model` for `from()`, `toArray()`, `toJson()`
- `HasFieldRules` trait — Extracts Laravel validation rules from `#[Field]` attributes via reflection
- `RendersRoute` trait — Builds URLs with path params and query strings
- `functions.php` — Global helpers: `api()`, `web()`, `api_response()`, `render_url()`

### API Response Pattern

All API responses go through `api_response()` which returns standardized JSON:
```json
{"success": true, "message": "...", "type": "...", "data": {...}, "errors": []}
```

### Test Factory Pattern

Uses [`zero-to-prod/data-model-factory`](https://github.com/zero-to-prod/data-model-factory) (not Laravel factories). Factories live alongside their modules in a `Factories/` subdirectory.

**Factory class** — uses `DataModelFactory` trait, sets `$model`, implements `definition()` and `make()`:

```php
use Zerotoprod\DataModelFactory\DataModelFactory;

class RegisterFormFactory
{
    use DataModelFactory;

    protected $model = RegisterForm::class;

    public function definition(): array
    {
        return [
            RegisterForm::name => RegisterForm::name,
            RegisterForm::email => 'john@example.com',
            RegisterForm::password => RegisterForm::password,
            RegisterForm::password_confirmation => RegisterForm::password,
        ];
    }

    public function make(): RegisterForm
    {
        return $this->instantiate();
    }
}
```

**DataModel class** — exposes a static `factory()` convenience method:

```php
readonly class RegisterForm
{
    use DataModel;

    // ... properties ...

    public static function factory(array $context = []): RegisterFormFactory
    {
        return RegisterFormFactory::factory($context);
    }
}
```

**Usage** — call `factory()` on the model, override state with `set()`, then `make()`:

```php
// Default values from definition()
$form = RegisterForm::factory()->make();

// Override specific values using property constants
$form = RegisterForm::factory()
    ->set(RegisterForm::name, 'Jane')
    ->set(RegisterForm::email, 'jane@example.com')
    ->make();

// Nested values via dot syntax
$model = Parent::factory()->set('address.street', '123 Main St')->make();

// Get raw context array without instantiating
$context = RegisterForm::factory()->context();
```

## DataModel Pattern

This project uses [`zero-to-prod/data-model`](https://github.com/zero-to-prod/data-model) wrapped by `App\Helpers\DataModel` (which adds `toArray()`, `toJson()`, `collect()`, `sanitize()`, `sanitizeEmail()`).

### Basic Structure

Every DataModel class follows this pattern:
1. `readonly` class
2. `use DataModel;` trait (from `App\Helpers\DataModel`)
3. String constants for each property (enables safe refactoring)
4. `/** @link $property */` docblock above each constant
5. `#[Describe]` for casting/defaults, `#[Field]` for API docs/validation

```php
readonly class ApiLoginRequest
{
    use DataModel;
    use HasFieldRules; // only for request/form objects that need validation

    /** @link $email */
    public const string email = 'email';
    #[Describe(['cast' => [self::class, 'sanitizeEmail']])]
    #[Field(description: 'User email address', rules: 'required|email')]
    public string $email;

    /** @link $password */
    public const string password = 'password';
    #[Field(description: 'User password', rules: 'required')]
    public string $password;
}
```

### Instantiation

```php
$ApiLoginRequest = ApiLoginRequest::from([
    ApiLoginRequest::email => 'USER@EXAMPLE.COM',
    ApiLoginRequest::password => 'secret',
]);
// $ApiLoginRequest->email === 'user@example.com' (sanitizeEmail applied via cast)
```

### `#[Describe]` Attribute Options

```php
#[Describe(['cast' => [self::class, 'method']])]   // transform value on hydration
#[Describe(['default' => 'value'])]                 // default if key missing
#[Describe(['default' => []])]                      // default empty array
#[Describe(['required' => true])]                   // throw if key absent
#[Describe(['nullable' => true])]                   // set null if missing
#[Describe(['from' => 'alternate_key'])]            // remap input key
#[Describe(['pre' => [self::class, 'validate']])]   // run before casting (void)
#[Describe(['post' => [self::class, 'log']])]       // run after casting (void)
#[Describe(['ignore'])]                             // skip property entirely
```

### Cast Method Signature

```php
private static function castMethod(
    mixed $value,
    array $context,
    ?\ReflectionAttribute $Attribute,
    \ReflectionProperty $Property
): string {
    return strtoupper($value);
}
```

Built-in sanitizers from `App\Helpers\DataModel`: `sanitize()` (squish whitespace), `sanitizeEmail()` (squish + lowercase).

### Response Models (No Validation)

For models that only represent output data, use `DataModel` without `HasFieldRules`:

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

### Recursive Hydration

Typed properties referencing other DataModel classes are automatically hydrated:

```php
$Parent = Parent::from([Parent::child => [Child::name => 'value']]);
// $Parent->child is a fully hydrated Child instance
```

## Conventions

- **Readonly classes**: All classes are `readonly` by default
- **Pint config**: Laravel preset, no spacing between constants/properties, one blank line between methods
- **Test suites**: `Behavior/` (integration), `Unit/`, `Feature/` — tests use `DatabaseTransactions` or `RefreshDatabase`
- **PHPUnit attributes**: Use `#[Test]` syntax, not `test_` method prefix convention
- **Sanitization**: Done via DataModel `#[Describe(['cast' => ...])]` attributes, not in controllers

## Style Rules (Non-Negotiable)

These rules are mandatory. Never deviate from them.

### 1. DaisyUI components only

Use DaisyUI component classes for all UI. Do not create custom components unless explicitly requested.

```html
<!-- CORRECT — DaisyUI classes -->
<button class="btn btn-primary">Submit</button>
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <input type="email" class="input input-bordered w-full" />
        <div class="alert alert-error">Invalid credentials</div>
    </div>
</div>

<!-- WRONG — custom component styles -->
<button class="rounded-lg bg-blue-600 px-4 py-2 text-white">Submit</button>
<div class="rounded-xl border p-6 shadow-lg">...</div>
```

### 2. PSR-12 baseline

Follow PSR-12 unless a rule below overrides it.

### 3. Variable casing matches the class name

When a variable holds an instance of a class, name it in the same case as the class:

```php
// CORRECT
$User = User::find(1);
$ApiLoginRequest = ApiLoginRequest::from(request()->all());
$Validator = Validator::make($ApiLoginRequest->toArray(), $ApiLoginRequest->rules());

// WRONG
$user = User::find(1);
$request = ApiLoginRequest::from(request()->all());
$validator = Validator::make(...);
```

### 4. Primitives are snake_case

Variables holding primitive values (string, int, float, bool, array) use snake_case:

```php
// CORRECT
$email = $ApiLoginRequest->email;
$is_valid = $Validator->passes();
$token_name = 'api-token';

// WRONG
$Email = $ApiLoginRequest->email;
$isValid = $Validator->passes();
```

### 5. Vertical alignment on argument names

Name variables to match the parameter they will be passed to:

```php
// CORRECT — variable names match argument names
$description = 'Authenticate and receive an API token.';
$errors      = [ErrorCode::invalid_credentials];

#[Endpoint(
    description: $description,
    errors: $errors,
)]

// CORRECT — aligning variables passed to a method
$data   = ApiToken::from([ApiToken::token => $plain_text_token]);
$status = 200;

return $this->respond(
    response: $data,
    status:   $status,
);
```

### 6. No magic strings in business logic

Back repeated or meaningful strings with an Enum or class constants. One-off strings (e.g. a single test value, a log message) are fine as literals.

```php
// CORRECT — meaningful values backed by Enum
return api_response()->unauthorized(ErrorCode::invalid_credentials);

// CORRECT — column access via constant
$User = User::where(User::email, $ApiLoginRequest->email)->first();

// CORRECT — one-off string is fine
Log::info('Login attempt failed');

// WRONG — magic string for a value that has a constant
$User = User::where('email', $ApiLoginRequest->email)->first();
```

### 7. Always use constants for column and property references

Never use raw strings for database columns or DataModel properties. Every Eloquent model must have a `Support\*Columns` trait defining its column constants. DataModel classes define their own property constants inline.

**Eloquent models** — extract column names into a `Support\{Model}Columns` trait:

```php
// app/Models/Support/UserColumns.php
trait UserColumns
{
    public const string name = 'name';
    public const string email = 'email';
    public const string password = 'password';
    public const string remember_token = 'remember_token';
    public const string email_verified_at = 'email_verified_at';
    public const string created_at = 'created_at';
    public const string updated_at = 'updated_at';
}

// app/Models/User.php
class User extends Authenticatable
{
    use UserColumns;

    protected $fillable = [
        self::name,
        self::email,
        self::password,
    ];

    protected $hidden = [
        self::password,
        self::remember_token,
    ];

    protected function casts(): array
    {
        return [
            self::email_verified_at => 'datetime',
            self::password => 'hashed',
        ];
    }
}
```

**DataModel classes** — constants defined inline on the class:

```php
// CORRECT
$ApiLoginRequest = ApiLoginRequest::from([
    ApiLoginRequest::email => 'user@example.com',
    ApiLoginRequest::password => 'secret',
    ApiLoginRequest::device_name => 'web',
]);

// CORRECT — queries use model constants
$User = User::where(User::email, $ApiLoginRequest->email)->first();

// WRONG — raw strings
$ApiLoginRequest = ApiLoginRequest::from(['email' => 'user@example.com']);
$User = User::where('email', $value);
```

This applies everywhere: production code, tests, factories, seeders, migrations, and queries. Use `self::` within the class, `ClassName::` externally.

### 8. Use constants in tests

Always use backed enum values and DataModel property constants in test assertions and setup — never raw strings:

```php
// CORRECT
$this->postJson(api()->login, [
    ApiLoginRequest::email => 'test@example.com',
    ApiLoginRequest::password => 'password',
    ApiLoginRequest::device_name => 'phpunit',
]);

$response->assertJsonPath(ApiResponse::success, true);
$response->assertJsonPath(ApiResponse::type, class_basename(ApiToken::class));

// WRONG
$this->postJson('/api/login', [
    'email' => 'test@example.com',
    'password' => 'password',
    'device_name' => 'phpunit',
]);

$response->assertJsonPath('success', true);
```

### 9. Migration columns must have comments

Every column in a migration must include a `->comment()` call. Tables must use `$table->comment()`.

```php
// CORRECT
Schema::create('orders', static function (Blueprint $Blueprint) {
    $Blueprint->comment('Customer orders placed through the storefront');
    $Blueprint->id()->comment('Primary key');
    $Blueprint->foreignId('user_id')->constrained()->comment('The customer who placed the order');
    $Blueprint->string('status')->comment('Current order status');
    $Blueprint->decimal('total', 10, 2)->comment('Order total in USD');
    $Blueprint->timestamps();
});

// WRONG — no comments
Schema::create('orders', static function (Blueprint $Blueprint) {
    $Blueprint->id();
    $Blueprint->foreignId('user_id')->constrained();
    $Blueprint->string('status');
    $Blueprint->decimal('total', 10, 2);
    $Blueprint->timestamps();
});
```