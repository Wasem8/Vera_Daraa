<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permissions = ['create', 'update', 'delete', 'view'];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $rolesWithPermissions = [
            'admin' => ['create', 'update', 'delete', 'view'],
            'client' => ['create', 'view'],
            'receptionist' => ['create', 'view', 'update'],
            'doctor' => ['view', 'update'],
            'accountant' => ['create', 'view'],
        ];


        foreach ($rolesWithPermissions as $roleName => $permissions) {

            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);

            $user = User::factory()->create([
                'name' => ucfirst($roleName),
                'email' => $roleName . '@example.com',
                'password' => bcrypt('12345678'),
            ]);

            $user->assignRole($role);
        }
    }
}
