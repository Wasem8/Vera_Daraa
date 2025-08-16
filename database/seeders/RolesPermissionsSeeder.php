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

        $adminRole=Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $clientRole=Role::create(['name' => 'client','guard_name' => 'web']);
        $receptionistRole=Role::create(['name' => 'receptionist','guard_name' => 'web']);
        $doctorRole=Role::create(['name' => 'doctor','guard_name' => 'web']);
        $accountantRole=Role::create(['name' => 'accountant','guard_name' => 'web']);


        $permissions = ['create','update','delete','view'];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission,'web');
        }

        $adminRole->givePermissionTo($permissions);
        $clientRole->givePermissionTo(['create','view']);
        $receptionistRole->givePermissionTo(['create','view','update']);


        $adminUser= User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('secret'),
        ]);
        $adminUser->assignRole($adminRole);

        $permissions = $adminRole->permissions()->pluck('name')->toArray();
        $adminUser->givePermissionTo($permissions);



        //////
        $receptionistUser= User::factory()->create([
            'name' => 'receptionist',
            'email' => 'receptionist@gmail.com',
            'password' => bcrypt('secret'),
        ]);
        $receptionistUser->assignRole($receptionistRole);

        $permissions = $receptionistRole->permissions()->pluck('name')->toArray();
        $receptionistUser->givePermissionTo($permissions);



        //////////////
        $clientUser= User::factory()->create([
            'name' => 'client',
            'email' => 'client@gmail.com',
            'password' => bcrypt('secret'),
        ]);
        $clientUser->assignRole($clientRole);

        $permissions = $clientRole->permissions()->pluck('name')->toArray();
        $clientUser->givePermissionTo($permissions);

        //////
        $doctorUser= User::factory()->create([
            'name' => 'Doctor',
            'email' => 'doctor@gmail.com',
            'password' => bcrypt('secret'),
        ]);
        $doctorUser->assignRole($doctorRole);
        /////
        $accountantUser= User::factory()->create([
            'name' => 'accountant',
            'email' => 'accountant@gmail.com',
            'password' => bcrypt('secret'),
        ]);
        $accountantUser->assignRole($accountantRole);
    }

}
