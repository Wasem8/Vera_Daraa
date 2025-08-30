<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Responses\Response;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Support\Facades\Auth;


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
        if(!$department){
            return Response::Error(null,'Department Not Found');
        }
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
            if(!Auth::user()->hasRole(['admin'])){
                return Response::Error(false,'you dont have permission to update department');
            }
            $updatedDepartment = $this->departmentService->update($updatedDepartmentRequest, $department);
            return Response::Success($updatedDepartment, 'Department updated successfully');
        }catch (\Exception $exception){
            return Response::Error($exception->getMessage(), $exception->getCode());
        }

    }
    public function destroy($departmentId)
    {

        $department = Department::query()->find($departmentId);
        if(!$department){
            return Response::Error(null,'Department Not Found');
        }
        if(!Auth::user()->hasRole(['admin'])){
            return Response::Error(false,'you dont have permission to delete department');
        }
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
