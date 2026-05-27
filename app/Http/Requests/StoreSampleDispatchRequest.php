<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSampleDispatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && ($user->hasFullSystemAccess() || $user->isDepartment('Clinical Operations'));
    }

    public function rules(): array
    {
        return [
            'dispatch_date'       => ['required', 'date'],
            'dispatch_time'       => ['nullable', 'date_format:H:i'],
            'participant_ids'     => ['required', 'array', 'min:1'],
            'participant_ids.*'   => ['required', 'string', 'max:100'],
            'no_of_bags'          => ['nullable', 'integer', 'min:1', 'max:9999'],
            'study'               => ['required', Rule::in(config('dispatch.studies'))],
            'visit'               => ['nullable', 'string', 'max:20'],
            'origin_location'     => ['required', Rule::in(config('dispatch.origins'))],
            'destination'         => ['required', Rule::in(config('dispatch.destinations'))],
            'driver_user_id'      => ['nullable', 'exists:users,id'],
            'driver_name'         => ['nullable', 'string', 'max:150'],
            'driver_phone'        => ['nullable', 'string', 'max:30'],
            'description'         => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination.in' => 'Please select a valid destination.',
        ];
    }
}
