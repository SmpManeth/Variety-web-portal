<x-app-layout>
    <div class="max-w-7xl mx-auto p-6" x-data="medicalRecordsPage()">

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Medical Records</h1>
                <p class="text-sm text-gray-500 mt-1">Displaying all medical records</p>
            </div>

            <button @click="openModal()"
                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition">
                <i class="fa-solid fa-file-medical"></i> Import Medical Records
            </button>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Records</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Import Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destroy Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($events as $event)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $event->title }}</td>
                        @if($event->medicalRecords->count() > 0)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $event->medicalRecords->count() }}
                                {{ Str::plural('record', $event->medicalRecords->count()) }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" title="{{ $event->medicalRecords->first()->imported_at }}">
                                {{ $event->medicalRecords->first()->imported_at?->diffForHumans() ?? 'Not imported' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $event->medicalRecords->first()->expires_at?->isPast() ? 'text-red-600' : 'text-gray-500' }}">
                                {{ $event->medicalRecords->first()->expires_at?->format('M j, Y') ?? 'Never' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <a
                                    href="{{ route('medical-records.show', $event) }}"
                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-gray-50">
                                    <i class="fa-solid fa-eye"></i>
                                    View Records
                                </a>
                            </td>
                        @else
                            <td colspan="3" class="px-6 py-4 text-sm text-center text-gray-400 italic">No Records Found</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <button
                                    type="button"
                                    @click="openModal({{ $event->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-gray-50">
                                    <i class="fa-solid fa-file-import"></i>
                                    Import Records
                                </button>
                            </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 whitespace-nowrap text-sm text-center text-gray-500">No events found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>

            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"  x-show="showModal" @click="closeModal()"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="showModal" class="relative w-full max-w-xl overflow-hidden rounded-xl bg-white shadow-2xl transition-all">

                    <div class="p-6">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Import Medical Records</h3>
                            <p class="text-sm text-gray-500">Upload a CSV file to associate records with an event.</p>
                        </div>

                        <form action="{{ route('medical-records.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Related Event</label>
                                <select name="event_id" x-model="form.event_id"
                                    class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                                    required>
                                    <option value="">-- Select an Event --</option>
                                    <template x-for="event in events" :key="event.id">
                                        <option :value="event.id" x-text="event.title" :selected="form.event_id == event.id"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CSV File</label>
                                <div class="rounded-lg border-2 border-dashed border-gray-300 p-4 text-center">
                                    <input type="file" name="csv_file" x-ref="csvInput" @change="handleCsvFile" accept=".csv,.txt" class="hidden">
                                    <button type="button" @click="$refs.csvInput.click()"
                                        class="inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                        <i class="fa-solid fa-folder-open text-gray-400"></i> Choose File
                                    </button>
                                    <p class="mt-2 text-xs text-gray-500" x-text="form.csv_filename || 'No file selected (.csv or .txt)'"></p>
                                </div>
                                <div class="mt-2">
                                    <a href="{{route('medical-records.template')}}" class="text-xs text-red-600 hover:text-red-700 font-medium underline">
                                        Download template
                                    </a>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Destroy Date</label>
                                <input type="date" name="destroy_date" x-model="form.destroy_date"
                                    class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"
                                    required>
                                <p class="text-[11px] text-gray-500 mt-1">Records will be automatically deleted on this date.</p>
                            </div>

                            <div class="flex items-start gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <input type="checkbox" name="acknowledge" x-model="form.acknowledge" id="acknowledge"
                                    class="mt-1 rounded border-gray-300 text-red-600 focus:ring-red-500" required>
                                <label for="acknowledge" class="text-xs text-gray-600 leading-relaxed">
                                    I understand that submitting this form will **overwrite** any existing medical records for the selected event.
                                </label>
                            </div>

                            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                                <button type="button" @click="closeModal()"
                                    class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                                    :disabled="!form.event_id || !form.csv_filename || !form.destroy_date || !form.acknowledge">
                                    <i class="fa-solid fa-upload mr-1"></i> Start Import
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function medicalRecordsPage() {
            return {
                showModal: false,
                events: @json($events), // Passes the events from PHP to JS
                form: {
                    event_id: '',
                    csv_filename: '',
                    destroy_date: '',
                    acknowledge: false
                },
                openModal(eventId = null) {
                    if (eventId) this.form.event_id = eventId;
                    this.showModal = true;
                    document.body.classList.add('overflow-hidden');
                },
                closeModal() {
                    this.showModal = false;
                    this.form = { event_id: '', csv_filename: '', destroy_date: '', acknowledge: false };
                    document.body.classList.remove('overflow-hidden');
                },
                handleCsvFile(e) {
                    const file = e.target.files[0];
                    this.form.csv_filename = file ? file.name : '';
                }
            }
        }
    </script>
</x-app-layout>
