<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The tables spatie/laravel-permission reads, spelled out rather than derived
 * from config: the table and column names it is pointed at are the ones the
 * table enums in app/Sources/Db/App mirror, so they are fixed here.
 *
 * `model_id` is a ULID because that is what `users.id` is. Teams are off, so no
 * team key is written.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', static function (Blueprint $Blueprint) {
            $Blueprint->id()->comment('The unique identifier of the permission');
            $Blueprint->string('name')->comment('The name the permission is granted by');
            $Blueprint->string('guard_name')->comment('The authentication guard the permission applies to');
            $Blueprint->timestamp('created_at')->nullable()->comment('When the permission was created');
            $Blueprint->timestamp('updated_at')->nullable()->comment('When the permission was last updated');

            $Blueprint->unique(['name', 'guard_name']);
        });

        Schema::create('roles', static function (Blueprint $Blueprint) {
            $Blueprint->id()->comment('The unique identifier of the role');
            $Blueprint->string('name')->comment('The name the role is granted by');
            $Blueprint->string('guard_name')->comment('The authentication guard the role applies to');
            $Blueprint->timestamp('created_at')->nullable()->comment('When the role was created');
            $Blueprint->timestamp('updated_at')->nullable()->comment('When the role was last updated');

            $Blueprint->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', static function (Blueprint $Blueprint) {
            $Blueprint->unsignedBigInteger('permission_id')->comment('The permission that is granted');
            $Blueprint->string('model_type')->comment('The class of the model the permission is granted to');
            $Blueprint->char('model_id', 26)->comment('The identifier of the model the permission is granted to');

            $Blueprint->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $Blueprint->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $Blueprint->primary(
                ['permission_id', 'model_id', 'model_type'],
                'model_has_permissions_permission_model_type_primary',
            );
        });

        Schema::create('model_has_roles', static function (Blueprint $Blueprint) {
            $Blueprint->unsignedBigInteger('role_id')->comment('The role that is granted');
            $Blueprint->string('model_type')->comment('The class of the model the role is granted to');
            $Blueprint->char('model_id', 26)->comment('The identifier of the model the role is granted to');

            $Blueprint->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            $Blueprint->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $Blueprint->primary(
                ['role_id', 'model_id', 'model_type'],
                'model_has_roles_role_model_type_primary',
            );
        });

        Schema::create('role_has_permissions', static function (Blueprint $Blueprint) {
            $Blueprint->unsignedBigInteger('permission_id')->comment('The permission that is granted');
            $Blueprint->unsignedBigInteger('role_id')->comment('The role the permission is granted to');

            $Blueprint->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $Blueprint->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $Blueprint->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
