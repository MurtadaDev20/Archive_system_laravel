<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SpatiePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'documents.view', 'documents.create', 'documents.update', 'documents.delete',
            'documents.download', 'documents.approve', 'documents.reject', 'documents.archive',
            'documents.transfer', 'documents.version', 'documents.restore',
            'departments.manage', 'categories.manage', 'users.manage', 'audit.view',
            'dashboard.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'Super Admin' => array_values(array_diff($permissions, ['documents.create'])),
            'Admin' => array_values(array_diff($permissions, ['documents.create'])),
            'Department Manager' => [
                'documents.view', 'documents.create', 'documents.update', 'documents.download',
                'documents.approve', 'documents.reject', 'documents.transfer', 'documents.version',
                'users.manage', 'dashboard.view', 'audit.view',
            ],
            'Employee' => [
                'documents.view', 'documents.create', 'documents.download', 'dashboard.view',
            ],
            'Viewer' => ['documents.view', 'documents.download', 'dashboard.view'],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }

        $legacyMap = [
            'Admin' => 'Super Admin',
            'Manager' => 'Department Manager',
            'Employee' => 'Employee',
            'Editor' => 'Employee',
        ];

        foreach (\App\Models\User::query()->with('legacyRoles')->get() as $user) {
            foreach ($user->legacyRoles as $legacyRole) {
                $spatieRole = $legacyMap[$legacyRole->name] ?? null;
                if ($spatieRole && ! $user->hasRole($spatieRole)) {
                    $user->assignRole($spatieRole);
                }
            }
        }
    }
}
