<?php

namespace App\Http\Controllers;

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



    public function show($id)
    {
        $department = Department::query()->find($id);
        return Response::success($department,'department retrieved successfully');
    }

    public function store(AddDepartmentRequest $request)
    {
        $departmentRequest = $request->validated();
        try {

            $department = $this->departmentService->create($departmentRequest);
            if($department != false){
                return Response::Success($department, 'Department created successfully');
            }else{
                return Response::Error(false,"you dont have permmission to add department");
            }

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


    public function servicesDepartment($departmentId){
        $department = Department::query()->with('services')->find($departmentId);
        $services = $department->services;
        if(!$department){
            return Response::Error(false,"Department not found");
        }
        return Response::Success($services,'Service List');
    }
}
