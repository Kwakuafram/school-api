<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AcademicPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'academics.view',
            'academics.create',
            'academics.update',
            'academics.delete',
            'teacher_assignments.view',
            'teacher_assignments.create',
            'teacher_assignments.update',
            'teacher_assignments.delete',
        ];

        $permissions = collect($permissionNames)
            ->mapWithKeys(fn (string $name) => [
                $name => Permission::query()->firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ]),
            ]);

        foreach (['super_admin', 'school_admin', 'principal'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if ($role) {
                $role->givePermissionTo($permissions->values()->all());
            }
        }

        $teacher = Role::query()
            ->where('name', 'teacher')
            ->where('guard_name', 'web')
            ->first();

        if ($teacher) {
            $teacher->givePermissionTo([
                $permissions['academics.view'],
                $permissions['teacher_assignments.view'],
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
