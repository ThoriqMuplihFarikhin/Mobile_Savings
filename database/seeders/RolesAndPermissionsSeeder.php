<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        // Create permissions
        $permissions = [
            'manage-nasabah',
            'verify-nasabah',
            'input-setoran',
            'koreksi-setoran',
            'approve-penarikan',
            'manage-produk',
            'manage-kolektor',
            'rekon-kas',
            'view-laporan',
            'manage-komplain',
            'view-log',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $kolektor = Role::firstOrCreate(['name' => 'kolektor', 'guard_name' => $guard]);
        $nasabah = Role::firstOrCreate(['name' => 'nasabah', 'guard_name' => $guard]);

        // Assign permissions to admin role via pivot table
        $adminPermissions = Permission::all();
        foreach ($adminPermissions as $permission) {
            DB::table('role_has_permissions')->updateOrInsert(
                ['permission_id' => $permission->id, 'role_id' => $admin->id],
                ['permission_id' => $permission->id, 'role_id' => $admin->id]
            );
        }

        // Assign permissions to kolektor role
        $kolektorPermissions = Permission::whereIn('name', ['input-setoran', 'manage-nasabah'])->get();
        foreach ($kolektorPermissions as $permission) {
            DB::table('role_has_permissions')->updateOrInsert(
                ['permission_id' => $permission->id, 'role_id' => $kolektor->id],
                ['permission_id' => $permission->id, 'role_id' => $kolektor->id]
            );
        }

        // Clear permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
