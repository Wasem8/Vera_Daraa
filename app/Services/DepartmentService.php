<?php

namespace App\Services;

use App\Http\Responses\Response;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class  DepartmentService
{
    public function create(array $data)
    {
        if(Auth::user()->hasRole(['admin','receptionist'])){
            if (Department::where('name', $data['name'])->exists()) {
                return Response::Validation([],'name is already exist');
            }
            return Department::create($data);
        }else{
            return false;
        }

    }
    public function update(array $data,Department $department)
    {
        return DB::transaction(function () use ($department, $data) {
           $department->update($data);
           return $department->refresh();
        });
    }
    public function destroy(Department $department)
    {
        DB::transaction(fn() =>  $department->delete());
    }

    public function list()
    {
        return Department::with('services')->get();
    }


}
