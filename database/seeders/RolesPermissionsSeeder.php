<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define roles and their respective permissions
        $rolesPermissions = [
            'admin' => ['create user', 'edit user', 'delete user', 'approve loan', 'create event'],
            'secretary' => ['record transactions', 'view all records'],
            'user' => ['view personal records', 'request loan', 'vote loan'],
        ];

        // Create roles and assign direct permissions
        foreach ($rolesPermissions as $role => $permissions) {
            $roleInstance = Role::findOrCreate($role);

            foreach ($permissions as $permissionName) {
                $permission = Permission::findOrCreate($permissionName);
                $roleInstance->givePermissionTo($permission);
            }
        }

        // Assign user permissions to secretary
        $userPermissions = Role::findByName('user')->permissions;
        $secretaryRole = Role::findByName('secretary');
        $secretaryRole->givePermissionTo($userPermissions);

        // Assign secretary (which now includes user permissions) and admin permissions to admin
        $secretaryPermissions = Role::findByName('secretary')->permissions;
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo($secretaryPermissions);
    }
}
