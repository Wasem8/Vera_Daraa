<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
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
        $employee = $this->route('employee');
        $userId = $employee && $employee->user ? $employee->user->id : null;
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => "sometimes|required|string|email|max:255|unique:users,email,{$userId}",
            'specialty' => 'sometimes|required|string|max:255',
            'department_id' => 'sometimes|required|exists:departments,id',
            'hire_date' => 'sometimes|required|date',
        ];
    }
}
