<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeFilterReques;
use App\Http\Requests\EmployeeSearchRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Responses\Response;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    protected $employeeService;
    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;


    }
    public function store(StoreEmployeeRequest $request)
    {
        try {
            $data = $request->validated();

            $employee = $this->employeeService->create($data);

            return Response::Success($employee, 'Employee created successfully');
        }
        catch (\Exception $e) {
            return Response::Error($e->getMessage(), $e->getCode());
        }

    }
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        try {
            if (!is_null($employee->archived_at)) {
                return Response::Error([] , 'Archived data can not updated');
            }

            $data = $request->validated();

            $updatedEmployee = $this->employeeService->update($employee, $data);

            return Response::success($updatedEmployee->fresh()->load('user'),'Employee updated successfully');

        }catch (\Exception $exception){
            return Response::Error($exception->getMessage(), $exception->getCode());
        }

    }

    public function index(EmployeeFilterReques $filter)
    {
        $filterResult   = $filter->validated();

        $employee = $this->employeeService->filterEmployees($filterResult);

        if ($employee->isEmpty()) {

           return Response::Error([], 'Employee not found');
        }

        return  Response::Success($employee,'Employee fetched successfully');

    }
    public function search(EmployeeSearchRequest $request)
    {
        $search = $request->input('search');

        $employees = $this->employeeService->SearchEmployees($search);

        if ($employees->isEmpty()) {
            return Response::Error([], 'Employees not found');
        }

        return Response::Success($employees,'Employee fetched successfully');


    }

    public function archive($id)
    {
        try {
            $employee = $this->employeeService->toggleArchive($id);

            return Response::Success($employee,'Employee archived successfully');
        }catch (\Exception $exception){
            return Response::Error($exception->getMessage(), $exception->getCode());
        }

    }

    public function showArchive()
    {
        $archives = Employee::all()->where('archived_at',true);
        return Response::Success($archives->load('user'),'Employee archived successfully');
    }

}
