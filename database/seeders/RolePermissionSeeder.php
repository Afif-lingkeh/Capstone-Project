<?php
// database/seeders/RolePermissionSeeder.php

namespace Database\Seeders;

use App\Enums\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $adminPermissions = [
            'programs.manage',
            'programs.view-interests',
            'links.manage',
            'links.verify',
            'articles.manage',
            'tests.manage',
            'alumni.verify',
            'consultations.moderate',
            'consents.manage',
            'counselor-shifts.manage',
        ];

        $rolePermissions = [
            // Super Admin TIDAK mendapat counseling.handle: admin tidak boleh membaca catatan konseling.
            Role::SuperAdmin->value => [...$adminPermissions, 'users.manage', 'audit-log.view'],
            Role::PesantrenAdmin->value => $adminPermissions,
            Role::Counselor->value => ['counseling.handle'],
            Role::AcademicAdvisor->value => ['guidance.manage-slots', 'advisees.view-results'],
            Role::Alumni->value => ['alumni-profile.manage'],
            Role::Santri->value => ['santri-features.use'],
        ];

        foreach (collect($rolePermissions)->flatten()->unique() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach ($rolePermissions as $roleName => $permissions) {
            SpatieRole::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }
    }
}