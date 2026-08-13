<?php

use App\Helpers\Role;
use App\Models\User;
use App\Sources\Db\App\Users;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates the `admin` role, and the one account that holds it.
 *
 * The role is always created. The account is only created when both
 * ADMIN_EMAIL and ADMIN_PASSWORD are set, so an environment that keeps no admin
 * credentials still gets a role for the middleware to check.
 *
 * Re-running is safe: the role and the user are matched on their natural keys
 * and updated in place, which is also how ADMIN_PASSWORD is rotated.
 */
return new class extends Migration
{
    public function up(): void
    {
        $guard = (string) config('auth.defaults.guard');

        DB::table('roles')->updateOrInsert(
            ['name' => Role::admin->value, 'guard_name' => $guard],
            ['updated_at' => now(), 'created_at' => now()],
        );

        $email = config('admin.email');
        $password = config('admin.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            return;
        }

        // The email is the natural key, so the id is read back rather than
        // written: re-running must not renumber the account.
        $id = DB::table('users')->where(Users::email->value, $email)->value(Users::id->value) ?? (string) Str::ulid();

        DB::table('users')->updateOrInsert([Users::id->value => $id], [
            Users::name->value => (string) config('admin.name'),
            Users::email->value => $email,
            Users::password->value => Hash::make($password),
            Users::email_verified_at->value => now(),
            Users::updated_at->value => now(),
            Users::created_at->value => now(),
        ]);

        DB::table('model_has_roles')->updateOrInsert([
            'role_id' => DB::table('roles')->where('name', Role::admin->value)->where('guard_name', $guard)->value('id'),
            'model_id' => $id,
            'model_type' => new User()->getMorphClass(),
        ]);
    }

    public function down(): void
    {
        $email = config('admin.email');

        if (is_string($email) && $email !== '') {
            DB::table('users')->where(Users::email->value, $email)->delete();
        }

        DB::table('roles')->where('name', Role::admin->value)->delete();
    }
};
