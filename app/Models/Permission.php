<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * The permission the permission package is pointed at, so that it generates a ULID.
 *
 * The package's own model leaves the key to the database, and the column is not
 * auto numbered, so an insert through it fails outright. Pointing the package
 * elsewhere, or dropping the trait, breaks every write that creates a permission.
 *
 * @property string $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin IdeHelperPermission
 */
class Permission extends SpatiePermission
{
    use HasUlids;
}
