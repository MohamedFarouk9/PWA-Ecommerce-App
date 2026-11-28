<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;
// 📌 This migration sets up all the tables needed by spatie/laravel-permission
// to handle Roles, Permissions, and their relationships.
return new class extends Migration
{
    public function up()
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');
        //Stores all permissions (like create-posts, delete-users, etc.).
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');            // Unique ID for each permission
            $table->string('name');                 // Permission name (e.g., 'edit articles')
            $table->string('guard_name');           // Guard name (e.g., 'web' or 'api')
            $table->timestamps();                   // Created at / Updated at
            $table->unique(['name', 'guard_name']); // Prevent duplicate names per guard
        });

        //Stores all roles (like Admin, Editor, User) that group permissions together.
        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id');            // Unique ID for each role

            // Optional: if "teams" feature is enabled, store team/tenant ID
            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }

            $table->string('name');                 // Role name (e.g., 'Admin', 'Editor')
            $table->string('guard_name');           // Guard name (e.g., 'web', 'api')
            $table->timestamps();                   // Created at / Updated at

            // Unique constraint (with team if enabled)
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        //Connects any model (like User, Admin, etc.) directly to permissions.
        //Example: Assign permission edit-posts directly to User 5.
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $teams) {
            $table->unsignedBigInteger('permission_id'); // Permission ID
            $table->string('model_type');                                      // Model class (e.g., App\Models\User)
            $table->unsignedBigInteger($columnNames['model_morph_key']);       // Model ID (user_id, etc.)

            // Index for faster lookups
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            // Foreign key to permissions table
            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            // Primary key (depends if teams enabled)
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');
                $table->primary([
                    $columnNames['team_foreign_key'],
                    'permission_id',
                    $columnNames['model_morph_key'],
                    'model_type'
                ], 'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([
                    'permission_id',
                    $columnNames['model_morph_key'],
                    'model_type'
                ], 'model_has_permissions_permission_model_type_primary');
            }
        });

        //Connects any model (like User) to roles.
        //Example: Assign role Admin to User 5.
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $teams) {
            $table->unsignedBigInteger('role_id');       // Role ID
            $table->string('model_type');                                      // Model class (e.g., App\Models\User)
            $table->unsignedBigInteger($columnNames['model_morph_key']);       // Model ID (user_id, etc.)

            // Index for faster lookups
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            // Foreign key to roles table
            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            // Primary key (depends if teams enabled)
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');
                $table->primary([
                    $columnNames['team_foreign_key'],
                    'role_id',
                    $columnNames['model_morph_key'],
                    'model_type'
                ], 'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([
                    'role_id',
                    $columnNames['model_morph_key'],
                    'model_type'
                ], 'model_has_roles_role_model_type_primary');
            }
        });

        //Connects roles with their assigned permissions.
        //Example: Role Admin has permission delete-users.
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger('permission_id'); // Permission ID
            $table->unsignedBigInteger('role_id');       // Role ID

            // Foreign key to permissions
            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            // Foreign key to roles
            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            // Composite primary key (role_id + permission_id must be unique)
            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        //Clears cached roles & permissions so changes take effect immediately.
        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down()
    {
        $tableNames = config('permission.table_names');
        if (empty($tableNames)) {
            throw new Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }
        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};

