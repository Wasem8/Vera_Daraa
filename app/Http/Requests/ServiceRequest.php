<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'department_id' => 'required|exists:departments,id',
            'duration' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $openingHour = 9;
                    $closingHour = 21;
                    $maxDurationInMinutes = ($closingHour - $openingHour) * 60;

                    if ($value > $maxDurationInMinutes) {
                        $fail("The service duration cannot exceed $maxDurationInMinutes minutes (working hours).");
                    }
                },
            ],
            'is_bookable' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
