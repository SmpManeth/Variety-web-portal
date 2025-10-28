<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create users') ?? true; // adjust as needed
    }

    public function rules(): array
    {
        return [
            'username'      => ['required', 'string', 'max:60', 'alpha_dash', 'unique:users,username'],
            'first_name'    => ['nullable', 'string', 'max:100'],
            'last_name'     => ['nullable', 'string', 'max:100'],
            'email'         => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'status'        => ['required', 'in:active,inactive'],
            'vehicle_code'  => ['nullable', 'string', 'max:50'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],

            'roles'         => ['array'],
            'roles.*'       => ['string', 'exists:roles,name'],

            'assigned_events'   => ['array'],
            'assigned_events.*' => ['integer', 'exists:events,id'],
        ];
    }
}
