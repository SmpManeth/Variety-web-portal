<x-app-layout>
    <div class="max-w-7xl mx-auto p-4">

        @php
            $participant = $record->participant;
            $firstName = $participant?->first_name ?? ($recordContent['first_name'] ?? '');
            $lastName = $participant?->last_name ?? ($recordContent['last_name'] ?? '');
            $displayName = trim(($firstName . ' ' . $lastName)) !== '' ? trim($firstName . ' ' . $lastName) : 'Participant';

            $dobInput = old('dob');
            if (($dobInput === null || $dobInput === '') && ! empty($recordContent['dob'])) {
                try {
                    $dobInput = \Carbon\Carbon::parse($recordContent['dob'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $dobInput = '';
                }
            }
            $expiresInput = old('expires_at', $record->expires_at->format('Y-m-d'));
        @endphp

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="items-center justify-between mb-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <a href="{{ route('medical-records.show-record', [$event, $record]) }}"
                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                    <i class="fa-solid fa-chevron-left"></i>
                    Back to record
                </a>

                <div class="flex gap-2">
                    <a href="{{ route('events.show', $event) }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-eye"></i> View Event
                    </a>
                </div>
            </div>
            <div class="mt-2">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Edit medical record</h1>
                <p class="text-gray-500 text-sm">{{ $displayName }}</p>
            </div>
        </div>

        <form action="{{ route('medical-records.update-record', [$event, $record]) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-xl bg-white p-4 shadow">
                <h3 class="text-lg font-bold mb-4">Retention</h3>
                <label class="block text-sm font-medium text-gray-700 mb-1">Destroy date</label>
                <input type="date" name="expires_at" value="{{ $expiresInput }}" required
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm w-full max-w-xs" />
                <p class="text-xs text-gray-500 mt-1">Required. Align with your organisation’s retention policy.</p>
            </div>

            <div class="flex gap-4 flex-col lg:flex-row">
                <div class="w-full lg:w-1/2 space-y-4">
                    <div class="rounded-xl bg-white p-4 shadow">
                        <h3 class="text-lg font-bold mb-4">Personal details</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">First name</label>
                                <input type="text" name="first_name"
                                    value="{{ old('first_name', $recordContent['first_name'] ?? '') }}"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last name</label>
                                <input type="text" name="last_name"
                                    value="{{ old('last_name', $recordContent['last_name'] ?? '') }}"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nickname</label>
                            <input type="text" name="nickname"
                                value="{{ old('nickname', $recordContent['nickname'] ?? '') }}"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle</label>
                            <input type="text" name="vehicle"
                                value="{{ old('vehicle', $recordContent['vehicle'] ?? '') }}"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                        @foreach (['address1', 'address2', 'address3', 'address4', 'address5', 'address6'] as $line)
                            <div class="mb-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address {{ substr($line, -1) }}</label>
                                <input type="text" name="{{ $line }}"
                                    value="{{ old($line, $recordContent[$line] ?? '') }}"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                            </div>
                        @endforeach
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile</label>
                            <input type="text" name="mobile"
                                value="{{ old('mobile', $recordContent['mobile'] ?? '') }}"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-4 shadow">
                        <h3 class="text-lg font-bold mb-4">Next of kin</h3>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="next_of_kin"
                                value="{{ old('next_of_kin', $recordContent['next_of_kin'] ?? '') }}"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact phone</label>
                            <input type="text" name="nok_phone"
                                value="{{ old('nok_phone', $recordContent['nok_phone'] ?? '') }}"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alternate contact phone</label>
                            <input type="text" name="nok_alt_phone"
                                value="{{ old('nok_alt_phone', $recordContent['nok_alt_phone'] ?? '') }}"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-4 shadow">
                        <h3 class="text-lg font-bold mb-4">Medical details</h3>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of birth</label>
                            <input type="date" name="dob" value="{{ $dobInput ?? '' }}"
                                class="rounded-lg border border-gray-300 px-3 py-2 text-sm w-full max-w-xs" />
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Allergies</label>
                            <textarea name="allergies" rows="3"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('allergies', $recordContent['allergies'] ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dietary requirements</label>
                            <textarea name="dietary_requirement" rows="3"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('dietary_requirement', $recordContent['dietary_requirement'] ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Past medical history</label>
                            <textarea name="past_medical_history" rows="3"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('past_medical_history', $recordContent['past_medical_history'] ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current medical history</label>
                            <textarea name="current_medical_history" rows="3"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('current_medical_history', $recordContent['current_medical_history'] ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current medications</label>
                            <textarea name="current_medications" rows="3"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">{{ old('current_medications', $recordContent['current_medications'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-1/2">
                    <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900">
                        <p class="font-semibold mb-1">Comments and images</p>
                        <p>After saving, you can add comments and images on the record detail page.</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 items-center pb-8">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700">
                    Save changes
                </button>
                <a href="{{ route('medical-records.show-record', [$event, $record]) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
