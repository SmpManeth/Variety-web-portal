<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Event
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['required', 'string'],
            'start_date'       => ['required', 'date'],
            'end_date'         => ['required', 'date', 'after_or_equal:start_date'],
            'max_participants' => ['required', 'integer', 'min:0'],

            // Days (existing/new)
            'days'                       => ['array'],
            'days.*.id'                  => ['nullable', 'integer'], // present for existing rows
            'days.*.title'               => ['required_with:days.*.date', 'string', 'max:255'],
            'days.*.date'                => ['required_with:days.*.title', 'date'],
            'days.*.subtitle'            => ['nullable', 'string', 'max:255'],
            'days.*.remove_image'        => ['sometimes', 'boolean'],
            'days.*.image'               => ['nullable', 'image', 'max:4096'],
            'days.*.sort_order'          => ['nullable', 'integer', 'min:0'],

            // Locations
            'days.*.locations'                   => ['array'],
            'days.*.locations.*.id'              => ['nullable', 'integer'],
            'days.*.locations.*.name'            => ['required_with:days.*.locations.*.link_title,days.*.locations.*.link_url', 'string', 'max:255'],
            'days.*.locations.*.link_title'      => ['nullable', 'string', 'max:255'],
            'days.*.locations.*.link_url'        => ['nullable', 'url'],
            'days.*.locations.*.sort_order'      => ['nullable', 'integer', 'min:0'],

            // Details
            'days.*.details'                     => ['array'],
            'days.*.details.*.id'                => ['nullable', 'integer'],
            'days.*.details.*.title'             => ['required_with:days.*.details.*.description', 'string', 'max:255'],
            'days.*.details.*.description'       => ['nullable', 'string'],
            'days.*.details.*.sort_order'        => ['nullable', 'integer', 'min:0'],

            // Resources
            'days.*.resources'                   => ['array'],
            'days.*.resources.*.id'              => ['nullable', 'integer'],
            'days.*.resources.*.title'           => ['required_with:days.*.resources.*.url', 'string', 'max:255'],
            'days.*.resources.*.url'             => ['nullable', 'url'],
            'days.*.resources.*.sort_order'      => ['nullable', 'integer', 'min:0'],

            // Sponsors
            'sponsors'                 => ['array'],
            'sponsors.*.id'            => ['nullable', 'integer'],
            'sponsors.*.name'          => ['required', 'string', 'max:255'],
            'sponsors.*.logo_url'      => ['nullable', 'url'],
            'sponsors.*.sort_order'    => ['nullable', 'integer', 'min:0'],
        ];
    }
}
