<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class AddDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

       public function rules(): array
    {
        return [
            'name' => 'required|string|max:25|unique:departments,name',
            'supervisor_id' => 'nullable|exists:employees,id',
            'suite_no' => 'required|string|max:25',
            'description' => 'required|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $supervisorId = $this->integer('supervisor_id');

            if ($supervisorId) {
                $employee = Employee::with('user.roles')->find($supervisorId);

                if (!$employee) {
                    $validator->errors()->add('supervisor_id', 'the selected supervisor does not exist');
                }elseif (!$employee->user || !$employee->user->hasRole('doctor')) {
                    $validator->errors()->add('supervisor_id', 'The supervisor must have the role "doctor".');
                }

            }
        });

    }

}
