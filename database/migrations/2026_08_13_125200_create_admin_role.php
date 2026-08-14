<?php

use App\Helpers\Role;
use App\Models\User;
use App\Sources\Db\App\Users;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $guard = (string) config('auth.defaults.guard');

        $roleId = DB::table('roles')
            ->where('name', Role::admin->value)
            ->where('guard_name', $guard)
            ->value('id') ?? (string) Str::ulid();

        DB::table('roles')->updateOrInsert(['id' => $roleId], [
            'name' => Role::admin->value,
            'guard_name' => $guard,
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        $email = config('admin.email');
        $password = config('admin.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            return;
        }

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
            'role_id' => $roleId,
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
