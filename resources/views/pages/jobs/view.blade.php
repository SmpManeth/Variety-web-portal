<x-app-layout>
    <div
        x-data="{
            importModalOpen: false,
            selectedEventId: @js($event->id),
            selectedEventTitle: @js($event->title),
            permitStoreUrlTemplate: @js(route('events.permits.store', ['event' => '__EVENT__'])),
            openImportModal(id, title) {
                this.selectedEventId = id;
                this.selectedEventTitle = title;
                this.importModalOpen = true;
            },
            closeImportModal() {
                this.importModalOpen = false;
            }
        }"
        x-effect="document.body.classList.toggle('overflow-hidden', importModalOpen)"
        @keydown.escape.window="closeImportModal()"
        class=" mx-auto">
        @if (session('success'))
            <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div>
            <h1 class="text-2xl font-bold text-gray-900">Jobs for {{ $event->title }}</h1>
            <p class="mt-1 text-sm text-gray-500">Displaying all jobs.</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">

            <div class="mb-3 flex flex-wrap items-center gap-2">
                <a href="{{ route('events.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-gray-50">
                    View events
                </a>
                <a href="{{ route('events.edit', $event) }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-gray-50">
                    Edit event
                </a>
                <button type="button"
                    @click="openImportModal({{ $event->id }}, @js($event->title))"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-gray-50">
                    Import jobs
                </button>
                <form action="{{ route('jobs.destroy', $event) }}" method="POST"
                    onsubmit="return confirm('Delete all jobs for this event?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50">
                        Delete jobs
                    </button>
                </form>
                <!-- Filters -->
                {{-- Filters: AM/PM kept for search (column hidden in table below) --}}
                <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">

                    <!-- Event Day -->
                    <div>
                        <label class="text-xs text-gray-500">Event Day</label>
                        <select name="event_day" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">All Days</option>

                            @foreach ($eventDays as $day)
                                <option value="{{ $day }}"
                                    {{ request('event_day') == $day ? 'selected' : '' }}>
                                    {{ $day }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Vehicle -->
                    <div>
                        <label class="text-xs text-gray-500">Vehicle</label>
                        <select name="vehicle" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">All Vehicles</option>

                            @foreach ($vehicles as $vehicle)
                                <option value="{{ $vehicle }}"
                                    {{ request('vehicle') == $vehicle ? 'selected' : '' }}>
                                    {{ $vehicle }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Period (AM/PM) -->
                    <div>
                        <label class="text-xs text-gray-500">AM/PM</label>
                        <select name="period" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">All</option>

                            @foreach ($periods as $period)
                                <option value="{{ $period }}"
                                    {{ request('period') == $period ? 'selected' : '' }}>
                                    {{ $period }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
                            Filter
                        </button>

                        <a href="{{ route('jobs.view', $event) }}"
                            class="px-4 py-2 border border-gray-300 text-sm rounded-lg hover:bg-gray-50">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-[1600px] w-full border-collapse text-sm text-gray-700">
                    {{-- Display order: job name, location, vehicle, [AM/PM hidden], OV dep, duty desc, notes, arrives, distance, then admin cols --}}
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold max-w-[200px]">
                                OV REGISTRATION <span class="font-normal text-gray-500">(Job name)</span>
                            </th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">Location</th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">Vehicle</th>
                            <th class="hidden border border-gray-200 px-3 py-2 text-left font-semibold" scope="col">AM/PM</th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">OV Departure</th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">Duty Description</th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">Notes</th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">OV Arrive</th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">Field Arrive</th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">Distance</th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">Event Day</th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">Image</th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">ID</th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">Created At</th>
                            <th class="border border-gray-200 px-3 py-2 text-left font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $job)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="border border-gray-200 px-3 py-2 font-medium">{{ $job->duty_code }}</td>
                                <td class="border border-gray-200 px-3 py-2">{{ $job->location }}</td>
                                <td class="border border-gray-200 px-3 py-2">{{ $job->vehicle }}</td>
                                <td class="hidden border border-gray-200 px-3 py-2">
                                    <span class="rounded px-2 py-1 text-xs font-bold {{ $job->period === 'AM' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                                        {{ $job->period }}
                                    </span>
                                </td>
                                <td class="border border-gray-200 px-3 py-2">{{ $job->ov_departure ?? '-' }}</td>
                                <td class="border border-gray-200 px-3 py-2">{{ $job->duty_description }}</td>
                                <td class="border border-gray-200 px-3 py-2 text-xs text-gray-600">
                                    {{ $job->comment }}
                                </td>
                                <td class="border border-gray-200 px-3 py-2">{{ $job->ov_arrive ?? '-' }}</td>
                                <td class="border border-gray-200 px-3 py-2">{{ $job->field_arrive ?? '-' }}</td>
                                <td class="border border-gray-200 px-3 py-2">{{ number_format($job->km, 2) }}</td>
                                <td class="border border-gray-200 px-3 py-2">{{ $job->event_day }}</td>
                                <td class="border border-gray-200 px-3 py-2">
                                    @if($job->image_path)
                                        <a href="/storage/{{ $job->image_path }}" target="_blank" class="text-sky-600 hover:underline font-medium">
                                            View Image
                                        </a>
                                    @else
                                        <form action="{{ route('jobs.upload-image', $job) }}" method="POST" enctype="multipart/form-data" class="upload-form">
                                            @csrf
                                            <input type="file" name="image" accept="image/*" onchange="this.form.submit()"
                                                   class="text-xs text-gray-400 italic">
                                        </form>
                                    @endif
                                </td>
                                <td class="border border-gray-200 px-3 py-2">{{ $job->id }}</td>
                                <td class="border border-gray-200 px-3 py-2">{{ $job->created_at->format('Y-m-d H:i') }}</td>
                                <td class="border border-gray-200 px-3 py-2">
                                    <a href="{{ route('jobs.edit', $job) }}"
                                        class="text-blue-600 hover:underline text-xs font-medium">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="border border-gray-200 px-3 py-10 text-center text-gray-500">
                                    No jobs found for this event.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4"> {{ $jobs->links() }} </div>
        </div>
        @include('pages.jobs.import-modal')
    </div>
</x-app-layout>
