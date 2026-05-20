<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSampleDispatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->isAdmin() || $user->department === 'Clinical Operations');
    }

    public function rules(): array
    {
        return [
            'dispatch_date'       => ['required', 'date'],
            'dispatch_time'       => ['nullable', 'date_format:H:i'],
            'sample_id'           => ['required', 'string', 'max:100'],
            'study'           => ['required', Rule::in(config('dispatch.studies'))],
            'origin_location' => ['required', Rule::in(config('dispatch.origins'))],
            'quantity'        => ['nullable', 'integer', 'min:1', 'max:9999'],
            'destination'     => ['required', Rule::in(config('dispatch.destinations'))],
            'driver_user_id'  => ['nullable', 'exists:drivers,id'],
            'driver_name'     => ['required', 'string', 'max:150'],
            'driver_phone'    => ['nullable', 'string', 'max:30'],
            'notes'           => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination.in' => 'Please select a valid destination.',
        ];
    }
}
