<?php

// app/Http/Requests/StoreEventRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string'],
            'start_date'       => ['required', 'date'],
            'end_date'         => ['required', 'date', 'after_or_equal:start_date'],
            'max_participants' => ['required', 'integer', 'min:0'],

            'days'                   => ['array'],
            'days.*.title'           => ['required_with:days.*.date', 'string', 'max:255'],
            'days.*.date'            => ['required_with:days.*.title', 'date'],
            'days.*.subtitle'        => ['nullable', 'string', 'max:255'],
            'days.*.image'           => ['nullable', 'image', 'max:4096'],

            'days.*.locations'                => ['array'],
            'days.*.locations.*.name'         => ['required_with:days.*.locations.*.link_title,days.*.locations.*.link_url', 'string', 'max:255'],
            'days.*.locations.*.link_title'   => ['nullable', 'string', 'max:255'],
            'days.*.locations.*.link_url'     => ['nullable', 'url'],

            'days.*.details'                  => ['array'],
            'days.*.details.*.title'          => ['required_with:days.*.details.*.description', 'string', 'max:255'],
            'days.*.details.*.description'    => ['nullable', 'string'],

            'days.*.resources'                => ['array'],
            'days.*.resources.*.title'        => ['required_with:days.*.resources.*.url', 'string', 'max:255'],
            'days.*.resources.*.url'          => ['nullable', 'url'],

            'sponsors'                => ['array'],
            'sponsors.*.name'         => ['required', 'string', 'max:255'],
            'sponsors.*.logo_url'     => ['nullable', 'url'],
        ];
    }
}
