<x-app-layout>

    <div class="max-w-4xl mx-auto p-6">

        <h1 class="text-2xl font-bold mb-6">Edit Job</h1>

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('jobs.update', $job) }}" method="POST" enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">OV REGISTRATION <span class="font-normal text-gray-500">(Job name)</span></label>
                    <input type="text" name="duty_code" value="{{ old('duty_code', $job->duty_code) }}"
                        class="mt-1 w-full border rounded p-2">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" value="{{ old('location', $job->location) }}"
                        class="mt-1 w-full border rounded p-2">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Vehicle</label>
                    <input type="text" name="vehicle" value="{{ old('vehicle', $job->vehicle) }}"
                        class="mt-1 w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">OV Departure</label>
                    <input type="time" name="ov_departure" step="60"
                        value="{{ old('ov_departure', $job->timeForInput('ov_departure')) }}"
                        class="mt-1 w-full border rounded p-2">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Duty Description</label>
                    <input type="text" name="duty_description" value="{{ old('duty_description', $job->duty_description) }}"
                        class="mt-1 w-full border rounded p-2">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="comment" class="mt-1 w-full border rounded p-2" rows="3">{{ old('comment', $job->comment) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">OV Arrive</label>
                    <input type="time" name="ov_arrive" step="60"
                        value="{{ old('ov_arrive', $job->timeForInput('ov_arrive')) }}"
                        class="mt-1 w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Field Arrive</label>
                    <input type="time" name="field_arrive" step="60"
                        value="{{ old('field_arrive', $job->timeForInput('field_arrive')) }}"
                        class="mt-1 w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Distance</label>
                    <input type="number" step="0.01" name="km"
                        value="{{ old('km', $job->km) }}"
                        class="mt-1 w-full border rounded p-2">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Event Day</label>
                    <input type="number" name="event_day" value="{{ old('event_day', $job->event_day) }}"
                        class="mt-1 w-full border rounded p-2">
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Image (File Name)</label>
                    <input type="text" name="image" value="{{ old('image', basename($job->image_path)) }}" class="mt-1 w-full border rounded p-2">
                </div>

            </div>

            {{-- Stored for search / filters; not shown (redundant with times in the table view) --}}
            <div class="hidden" aria-hidden="true">
                <select name="period" tabindex="-1">
                    <option value="AM" {{ old('period', $job->period) == 'AM' ? 'selected' : '' }}>AM</option>
                    <option value="PM" {{ old('period', $job->period) == 'PM' ? 'selected' : '' }}>PM</option>
                </select>
            </div>

            <div class="mt-6 flex gap-3">

                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">
                    Update Job
                </button>

                <a href="{{ route('jobs.view', $job->event_id) }}" class="border px-4 py-2 rounded">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</x-app-layout>
