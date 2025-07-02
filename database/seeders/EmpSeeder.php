<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'doctor']);

        $user = User::create([
            'name' => 'Dr. Ahmad',
            'email' => 'ahmed@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        $user->assignRole($role->name);
        $employee = Employee::create([
            'user_id' => $user->id,
            'specialty' => 'Dermatologist',
            'department_id' => 1,
            'hire_date' => now()->toDateString(),
            'archived_at' => Carbon::now()->toDateTimeString(),
        ]);

    }
}
