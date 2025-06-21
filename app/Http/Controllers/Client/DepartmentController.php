<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function showDepartments(){
        $departments = Department::all();
        return Response::success($departments,'departments retrieved successfully');
    }

    public function showDepartment($id)
    {
        $department = Department::query()->find($id);
        return Response::success($department,'department retrieved successfully');
    }

    public function showServices($id)
    {
        $services = Department::query()->find($id)->services;
        return Response::success($services,'services retrieved successfully');
    }
}
