<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Responses\Response;
use App\Models\Department;
use App\Services\DepartmentService;


class DepartmentController extends Controller
{
    protected DepartmentService $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index(){
        try {
               $departments = $this->departmentService->list();
                return Response::Success($departments,'Department List');
        }catch (\Exception $e){
                return Response::Error($e->getMessage(),$e->getCode());
        }
    }

    public function store(AddDepartmentRequest $request)
    {
        $departmentRequest = $request->validated();
        try {

            $department = $this->departmentService->create($departmentRequest);

            return Response::Success($department, 'Department created successfully');
        }catch (\Exception $exception){
            return Response::Error($exception->getMessage(), $exception->getCode());
        }
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $updatedDepartmentRequest = $request->validated();
        try {
            $updatedDepartment = $this->departmentService->update($updatedDepartmentRequest, $department);
            return Response::Success($updatedDepartment, 'Department updated successfully');
        }catch (\Exception $exception){
            return Response::Error($exception->getMessage(), $exception->getCode());
        }

    }
    public function destroy(Department $department)
    {
        $this->departmentService->destroy($department);
        return Response::Success([],'Department deleted successfully');

    }
}
