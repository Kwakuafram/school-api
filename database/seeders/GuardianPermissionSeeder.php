<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GuardianPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'guardians.view',
            'guardians.create',
            'guardians.update',
            'guardians.delete',
            'students.guardians.manage',
        ];

        $permissions = collect($permissionNames)
            ->mapWithKeys(fn (string $name) => [
                $name => Permission::query()->firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ]),
            ]);

        $schoolAdmin = Role::query()
            ->where('name', 'school_admin')
            ->where('guard_name', 'web')
            ->first();

        if ($schoolAdmin) {
            $schoolAdmin->givePermissionTo($permissions->values()->all());
        }

        $superAdmin = Role::query()
            ->where('name', 'super_admin')
            ->where('guard_name', 'web')
            ->first();

        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions->values()->all());
        }

        $teacher = Role::query()
            ->where('name', 'teacher')
            ->where('guard_name', 'web')
            ->first();

        if ($teacher) {
            $teacher->givePermissionTo([
                $permissions['guardians.view'],
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
