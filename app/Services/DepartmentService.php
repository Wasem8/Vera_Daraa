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
            return Department::create($data);
    }
    public function update(array $data,Department $department)
    {
        return DB::transaction(function () use ($department, $data) {
           $department->update($data);
           return $department->refresh();
        });
    }
    public function destroy($departmentId)
    {
        $department = Department::query()->findOrFail($departmentId);
        DB::transaction(fn() =>  $department->delete());
    }

}
