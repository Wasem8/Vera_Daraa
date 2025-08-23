<?php

namespace App\Services;

use App\Http\Responses\Response;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmployeeService
{
    public function create(array $data): Employee
    {
       return DB::transaction(function () use ($data) {
           $user = User::create([
               'name' => $data['name'],
               'email' => $data['email'],
               'password' => Hash::make($data['password']),
           ]);

           if (!empty($data['role'])) {
               $user->assignRole($data['role']);
           }
           $employee = Employee::create([
               'user_id' => $user->id,
               'department_id' => $data['department_id'],
               'hire_date' => $data['hire_date'],
               'archived_at' => null,
           ]);


           return $employee->load('user');
       });
    }



    public function update(Employee $employee, array $data): Employee
    {
         DB::transaction(function () use ($employee, $data) {
            $userData = [];
            if (array_key_exists('name', $data)) {
                $userData['name'] = $data['name'];
            }
            if (array_key_exists('email', $data)) {
                $userData['email'] = $data['email'];
            }
            Log::info('User data to update:', $userData);
            if (!empty($userData)) {
                $employee->user->update($userData);
            }


            $employeeData = [];
            foreach ([ 'department_id', 'hire_date'] as $key) {
                if (array_key_exists($key, $data)) {
                    $employeeData[$key] = $data[$key];
                }
            }
            Log::info('Employee data to update:', $employeeData);
            if (!empty($employeeData)) {
                $employee->update($employeeData);
            }

        });
    return $employee->load('user');
          }

    public function filterEmployees(array $filters)
    {
        $employee = Employee::with(['user'])
            ->when(isset($filters['department_id']),function ($q) use ($filters){
                $q->where('department_id',$filters['department_id']);
            })
            ->when(isset($filters['status']), function ($q) use ($filters) {
                $status = strtolower($filters['status']);
                if ($status == 'active') {
                    $q->whereHas('user', function ($query) {
                        $query->where('is_active', 1);
                    });
                } elseif ($status == 'inactive') {
                    $q->whereHas('user', function ($query) {
                        $query->where('is_active', 0);
                    });
                }
            })
            ->when(isset($filters['role']),function ($q) use ($filters){
                $q->whereHas('user.roles', function ($q) use ($filters) {
                    $q->where('name', $filters['role']);
                });
            })->get();

        return $employee;
    }

    public function SearchEmployees(string $search)
    {
        return Employee::with(['user'])
            ->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })->get();
    }

    public function toggleArchive($id)
    {
        $employee = Employee::with('user')->findOrFail($id);

        if (is_null($employee->archived_at)) {
          $employee->update(['archived_at'=>now()]);
          $employee->user()->update(['is_active' => false]);

        }else{
            $employee->update(['archived_at'=>null]);
            $employee->user()->update(['is_active' => true]);

        }

        return $employee->load('user');

    }
}


