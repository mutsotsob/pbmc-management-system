<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email,' . $userId],
            'department'   => ['nullable', Rule::in(config('departments'))],
            'job_title'    => ['nullable', 'string', 'max:255'],
            'user_type'    => ['required', 'in:admin,user'],
            'user_status'  => ['required', 'boolean'],
            'phone_number' => ['nullable', 'string', 'max:50'],
        ];
    }
}
