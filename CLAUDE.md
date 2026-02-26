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

# Rector (automated refactoring)
sail php vendor/bin/rector process            # apply rules
sail php vendor/bin/rector process --dry-run  # preview changes

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

Routes are defined as singleton classes with string constants, accessed via helpers: `api()->login`, `web()->register`.

**Static routes** — simple string properties accessed directly:

```php
public string $posts = self::prefix.'/posts';

// Usage
$this->getJson(api()->posts);
```

**Dynamic routes** — use `{param}` placeholders (Laravel route syntax) with a dedicated route class and a helper method on `ApiRoutes`. The route class uses `RendersRoute` and defines a constant for each parameter. The helper method returns a hydrated route instance whose `render()` method resolves the URL:

```php
// app/Routes/PostRoute.php
class PostRoute
{
    use RendersRoute;

    public const string post = 'post';
}

// app/Routes/ApiRoutes.php
public string $posts_show = self::prefix.'/posts/{'.PostRoute::post.'}';

public function post(string $post): PostRoute
{
    return PostRoute::from([
        PostRoute::route => $this->posts_show,
        PostRoute::path_params => [PostRoute::post => $post],
    ]);
}

// Usage (tests, controllers, etc.)
api()->post($Post->id)->render()  // → 'api/posts/42'
```

The `{param}` syntax serves double duty: Laravel uses it for route registration, and `render_url()` replaces it for URL generation.

### Key Helpers (`app/Helpers/`)

- `DataModel` trait — Wraps `zero-to-prod/data-model` for `from()`, `toArray()`, `toJson()`
- `HasFieldRules` trait — Extracts Laravel validation rules from `#[Field]` attributes via reflection
- `RendersRoute` trait — Builds URLs with path params and query strings
- `RunsQuery` trait — Dispatches an event and delegates to `handle()` for query objects
- `functions.php` — Global helpers: `api()`, `web()`, `api_response()`, `build_schema()`, `render_url()`

### Query Objects (`app/Queries/`)

All database queries live in dedicated query classes under `app/Queries/`. Each query class uses the `RunsQuery` trait, which provides a static `get()` entry point that dispatches an event and delegates to `handle()`.

**Query class** — uses `RunsQuery`, implements `handle()` with only primitives or DataModel parameters (never Eloquent models):

```php
// app/Queries/PostShow.php
use App\Helpers\RunsQuery;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method static null|Post get(string $user_id, string $post)
 */
class PostShow
{
    use RunsQuery;

    public function handle(string $user_id, string $post): ?Post
    {
        return Post::query()
            ->where(Post::user_id, $user_id)
            ->where(function (Builder $Query) use ($post) {
                $Query->where(Post::id, $post)
                    ->orWhere(Post::slug, $post);
            })
            ->first();
    }
}
```

**Usage in controllers:**

```php
$Post = PostShow::get(request()->user()->id, $ShowPostRequest->post);
```

**Key rules:**
- One query per class, one `handle()` method
- **One side effect per query class** — a query that creates a Tag must not also attach a pivot row. Split into separate query classes (e.g. `TagStore` + `TagAttach`). The controller orchestrates multiple queries, not the query itself
- Only accept primitives (`string`, `int`, `bool`, `array`) or DataModel instances as parameters — never Eloquent models, `User`, `Request`, etc.
- **Queries must not derive or transform values** — pass pre-computed values as arguments. If a slug is needed, compute it in the controller and pass both `$name` and `$slug` to the query. The query is a data access layer, not a business logic layer
- **Never inline table names** — use Eloquent model classes or Pivot model classes (e.g. `PostTag::class`) instead of raw strings like `'post_tag'`. This applies to queries, assertions (`assertDatabaseHas`), and `DB::table()` calls
- Add a `@method` docblock for IDE autocomplete of the static `get()` call
- Tests go in `tests/Unit/Queries/` and test the query class directly via `QueryClass::get()`

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

### Typed Collections (`mapOf`)

When a DataModel property holds a collection of typed objects, use the `mapOf` cast from `DataModelHelper` to automatically hydrate each element. This replaces manual `->map()` calls in controllers:

```php
use Illuminate\Support\Collection;

/** @link $Posts */
public const string Posts = 'Posts';
/** @var Collection<int, PostResource> */
#[Describe([
    'cast' => [self::class, 'mapOf'],
    'type' => PostResource::class,
])]
#[Field('Collection of post resources')]
public Collection $Posts;
```

Pass raw arrays — `mapOf` handles hydration into the target type:

```php
// CORRECT — let mapOf hydrate each item
PaginatedPosts::from([
    PaginatedPosts::Posts => $raw_arrays,
]);

// WRONG — manually mapping before hydration
PaginatedPosts::from([
    PaginatedPosts::Posts => collect($models)->map(fn ($m) => PostResource::from($m->toArray()))->all(),
]);
```

Available `mapOf` options: `type` (target class/enum), `coerce` (wrap single element in array), `level` (nesting depth), `key_by` (key associative array by field), `using`/`map_via` (custom mapping).

## Conventions

- **Readonly classes**: All classes are `readonly` by default
- **Pint config**: Laravel preset, no spacing between constants/properties, one blank line between methods
- **Test suites**: `Behavior/` (integration), `Unit/`, `Feature/` — tests use `DatabaseTransactions` or `RefreshDatabase`
- **PHPUnit attributes**: Use `#[Test]` syntax, not `test_` method prefix convention
- **Sanitization**: Done via DataModel `#[Describe(['cast' => ...])]` attributes, not in controllers
- **Unguarded models**: All Eloquent models use `protected static $unguarded = true;` instead of `$fillable`. Do not use `$fillable` or `$guarded` unless explicitly specified
- **Collection property naming**: Name collection properties after what they contain (PascalCase, since `Collection` is a class instance), not generically. Never use `$items`, `$data`, `$results`, `$list`:

```php
// CORRECT — PascalCase, named after the domain object
public Collection $Posts;
public Collection $Orders;

// WRONG — generic or lowercase
public Collection $items;
public Collection $data;
public Collection $posts;
```
- **Typed closure params**: Always type-hint closure/arrow function parameters when the type is known. Rector enforces this via `AddClosureParamTypeFromIterableMethodCallRector` for iterable method calls (e.g. `Collection::map()`):

```php
// CORRECT
->map(static fn (Post $Post) => PostResource::from($Post->toArray()))

// WRONG — missing type hint
->map(static fn ($Post) => PostResource::from($Post->toArray()))
```

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

**Eloquent models** — extract column names into a `Support\{Model}Columns` trait. Always include `id`:

```php
// app/Models/Support/PostColumns.php
trait PostColumns
{
    public const string id = 'id';
    public const string user_id = 'user_id';
    public const string title = 'title';
    public const string slug = 'slug';
    public const string body = 'body';
    public const string published_at = 'published_at';
    public const string created_at = 'created_at';
    public const string updated_at = 'updated_at';
}
```

**Relationship constants** — define on the model class itself (not the columns trait), grouped under a `// Relationships` comment. The constant value must match the method name:

```php
// app/Models/Post.php
class Post extends Model
{
    use PostColumns;

    protected static $unguarded = true;

    // Relationships
    public const string user = 'user';
    public const string comments = 'comments';
    public const string tags = 'tags';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
```

**Pivot tables** — every pivot table gets a Pivot model class (e.g. `PostTag`) with a `Support\*Columns` trait defining its column constants. Use the model class in assertions and queries — never raw table name strings:

```php
// app/Models/Support/PostTagColumns.php
trait PostTagColumns
{
    public const string post_id = 'post_id';
    public const string tag_id = 'tag_id';
}

// app/Models/PostTag.php
class PostTag extends Pivot
{
    use PostTagColumns;

    protected $table = 'post_tag';
    public $incrementing = false;
    public $timestamps = false;
    protected static $unguarded = true;
}

// BelongsToMany relationships must reference the pivot model via ->using()
public function tags(): BelongsToMany
{
    return $this->belongsToMany(Tag::class)->using(PostTag::class);
}

// CORRECT — use PostTag model class and its column constants
$this->assertDatabaseMissing(PostTag::class, [
    PostTag::post_id => $Post->id,
    PostTag::tag_id  => $Tag->id,
]);

// WRONG — raw table name string
$this->assertDatabaseMissing('post_tag', [
    'post_id' => $Post->id,
    'tag_id'  => $Tag->id,
]);
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

### 10. Validation rules must enforce database column size limits

Every `#[Field]` validation rule for a string or text field **must** include a `max` rule matching the database column's maximum size. This prevents users from submitting data that exceeds what the database can store.

| DB Column Type | Max Rule |
|----------------|----------|
| `string()` / VARCHAR(255) | `max:255` |
| `string('col', N)` / VARCHAR(N) | `max:N` |
| `text()` / TEXT | `max:65535` |
| `mediumText()` | `max:16777215` |
| `longText()` | `max:4294967295` |
| `char(26)` / ULID | `max:26` |

```php
// CORRECT — max matches DB column size
#[Field(description: 'Post title', rules: 'required|string|max:255')]       // string() → VARCHAR(255)
#[Field(description: 'Post body', rules: 'required|string|max:65535')]      // text() → TEXT
#[Field(description: 'User email', rules: 'required|email|max:255')]        // string() → VARCHAR(255)
#[Field(description: 'Post ULID or slug', rules: 'required|string|max:255')]// max of possible inputs

// WRONG — no max, allows unbounded input
#[Field(description: 'Post title', rules: 'required|string')]
#[Field(description: 'Post body', rules: 'required|string')]
#[Field(description: 'User email', rules: 'required|email')]
```

### 11. Extract all database queries into query objects

Never inline Eloquent queries in controllers, middleware, or other non-query classes. Every database query must live in a dedicated class under `app/Queries/` using the `RunsQuery` trait. Query parameters must be primitives or DataModel instances — never Eloquent models or framework objects.

```php
// CORRECT — query extracted to a dedicated class
$Post = PostShow::get(request()->user()->id, $ShowPostRequest->post);

// WRONG — inline query in a controller
$Post = request()->user()
    ->posts()
    ->where(Post::id, $ShowPostRequest->post)
    ->orWhere(function (Builder $Query) use ($ShowPostRequest) {
        $Query->where(Post::slug, $ShowPostRequest->post)
            ->where(Post::user_id, request()->user()->id);
    })
    ->first();

// WRONG — passing Eloquent model to a query
$Post = PostShow::get($User, $post);  // $User is an Eloquent model

// CORRECT — pass the primitive ID instead
$Post = PostShow::get($User->id, $post);
```