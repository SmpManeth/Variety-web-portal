<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit users') ?? true;
    }

    public function rules(): array
    {
        $id = $this->route('user')->id;

        return [
            'username'      => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('users', 'username')->ignore($id)],
            'first_name'    => ['nullable', 'string', 'max:100'],
            'last_name'     => ['nullable', 'string', 'max:100'],
            'email'         => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($id)],
            'phone'         => ['nullable', 'string', 'max:50'],
            'status'        => ['required', 'in:active,inactive'],
            'vehicle_code'  => ['nullable', 'string', 'max:50'],
            'password'      => ['nullable', 'string', 'min:8', 'confirmed'],

            'roles'         => ['array'],
            'roles.*'       => ['string', 'exists:roles,name'],

            'assigned_events'   => ['array'],
            'assigned_events.*' => ['integer', 'exists:events,id'],
        ];
    }
}
