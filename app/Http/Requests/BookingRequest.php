<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
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
            'service_id'   => 'required|exists:services,id',
            'booking_date' => 'required|date|after:now',
            'notes'        => 'string|nullable|max:500',
            'offer_id'     => 'nullable|exists:offers,id',
            'user_id'      => 'nullable|exists:users,id',
        ];
    }
}
