<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'department' => ['nullable', Rule::in(config('departments'))],
            'job_title'  => ['nullable', 'string', 'max:255'],
            'user_type'  => ['required', 'in:admin,user'],
            'password'   => ['required', 'min:8', 'confirmed'],
        ];
    }
}
