<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permission;
use App\Support\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permission::all() as $permissionName) {
            PermissionModel::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        foreach (config('rbac.role_permissions', []) as $roleName => $permissions) {
            $role = RoleModel::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions);
        }

        // Keep first existing account from being locked out.
        User::query()
            ->whereDoesntHave('roles')
            ->oldest('id')
            ->first()?->assignRole(Role::SUPER_ADMIN);
    }
}
