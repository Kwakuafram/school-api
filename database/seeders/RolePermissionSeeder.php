<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'schools.view',
            'schools.create',
            'schools.update',
            'schools.delete',

            'campuses.view',
            'campuses.create',
            'campuses.update',
            'campuses.delete',

            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.activate',
            'users.suspend',

            'students.view',
            'students.create',
            'students.update',
            'students.delete',
            'students.promote',

            'teachers.view',
            'teachers.create',
            'teachers.update',
            'teachers.delete',

            'guardians.view',
            'guardians.create',
            'guardians.update',
            'guardians.delete',
            'students.guardians.manage',

            'attendance.view',
            'attendance.take',
            'attendance.approve',

            'results.view',
            'results.enter',
            'results.approve',
            'results.publish',

            'fees.view',
            'fees.manage',

            'payments.view',
            'payments.verify',

            'reports.view',
            'reports.export',

            'dashboard.view',

            'audit_logs.view',
            'audit_logs.export',
        ];

        $permissions = collect($permissionNames)
            ->mapWithKeys(function (string $name) {
                return [
                    $name => Permission::query()->firstOrCreate([
                        'name' => $name,
                        'guard_name' => 'web',
                    ]),
                ];
            });

        $roles = collect([
            'super_admin',
            'school_admin',
            'principal',
            'accountant',
            'teacher',
            'parent',
            'student',
        ])->mapWithKeys(function (string $name) {
            return [
                $name => Role::query()->firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ]),
            ];
        });

        $roles['super_admin']->syncPermissions($permissions->values()->all());

        $roles['school_admin']->syncPermissions($permissions->only([
            'schools.view',

            'campuses.view',
            'campuses.create',
            'campuses.update',

            'users.view',
            'users.create',
            'users.update',
            'users.activate',
            'users.suspend',

            'students.view',
            'students.create',
            'students.update',
            'students.delete',
            'students.promote',

            'teachers.view',
            'teachers.create',
            'teachers.update',

            'guardians.view',
            'guardians.create',
            'guardians.update',
            'guardians.delete',
            'students.guardians.manage',

            'attendance.view',
            'attendance.take',

            'results.view',
            'results.enter',

            'fees.view',
            'fees.manage',

            'payments.view',

            'reports.view',
            'reports.export',

            'dashboard.view',
            'audit_logs.view',
        ])->values()->all());

        $roles['principal']->syncPermissions($permissions->only([
            'students.view',
            'teachers.view',
            'guardians.view',
            'attendance.view',
            'attendance.approve',
            'results.view',
            'results.approve',
            'results.publish',
            'reports.view',
            'reports.export',
            'dashboard.view',
        ])->values()->all());

        $roles['accountant']->syncPermissions($permissions->only([
            'students.view',
            'guardians.view',
            'fees.view',
            'fees.manage',
            'payments.view',
            'payments.verify',
            'reports.view',
            'reports.export',
            'dashboard.view',
        ])->values()->all());

        $roles['teacher']->syncPermissions($permissions->only([
            'students.view',
            'attendance.view',
            'attendance.take',
            'results.view',
            'results.enter',
            'dashboard.view',
        ])->values()->all());

        $roles['parent']->syncPermissions($permissions->only([
            'students.view',
            'attendance.view',
            'results.view',
            'fees.view',
            'payments.view',
            'dashboard.view',
        ])->values()->all());

        $roles['student']->syncPermissions($permissions->only([
            'attendance.view',
            'results.view',
            'fees.view',
            'dashboard.view',
        ])->values()->all());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
