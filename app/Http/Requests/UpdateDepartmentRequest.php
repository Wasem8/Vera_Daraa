<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
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
        $id = $this->route('department')->id;
        return [
            'name' => 'required|string|unique:departments,name,'.$id,
            'description' => 'string|nullable',
            'suite_no' => 'string|required',
            'supervisor_id' => 'nullable|exists:employees,id',
        ];
    }
}
