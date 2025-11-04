<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEventParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create event participants') ?? true; // adjust as needed
    }

    public function rules(): array
    {
        return [
            'first_name'                   => ['required', 'string', 'max:100'],
            'last_name'                    => ['nullable', 'string', 'max:100'],
            'email'                        => ['nullable', 'email', 'max:255'],
            'phone'                        => ['nullable', 'string', 'max:50'],
            'vehicle'                      => ['nullable', 'string', 'max:255'],
            'status'                       => ['required', 'in:active,inactive'],
            'emergency_contact_name'       => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone'      => ['nullable', 'string', 'max:50'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
        ];
    }
}
