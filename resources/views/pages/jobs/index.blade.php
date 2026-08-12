<x-app-layout>
    <div
        x-data="{
            importModalOpen: false,
            selectedEventId: null,
            selectedEventTitle: '',
            openImportModal(id, title) {
                this.selectedEventId = id;
                this.selectedEventTitle = title;
                this.importModalOpen = true;
            },
            viewJobs(eventId) {
                window.location.href = this.jobsViewUrlTemplate.replace('__EVENT__', eventId);
            },
            closeImportModal() {
                this.importModalOpen = false;
            }
        }"
        x-effect="document.body.classList.toggle('overflow-hidden', importModalOpen)"
        @keydown.escape.window="closeImportModal()"
        class="max-w-7xl mx-auto p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">OV Jobs</h1>
            <p class="text-sm text-gray-500 mt-1">Jobs are synced from event uploads automatically.</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jobs</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($events as $event)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $event->title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $event->jobs->count() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <a
                                        href="{{ route('job-images.index', $event)}}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-gray-50">
                                        <i class="fa-regular fa-image"></i>
                                        Images
                                    </a>
                                    <button type="button"
                                        @click="openImportModal({{ $event->id }}, @js($event->title))"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-gray-50">
                                        <i class="fa-solid fa-file-import"></i>
                                        Import Jobs
                                    </button>
                                    <a
                                        type="button"
                                        href="{{ route('jobs.view', $event)}}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-gray-50">
                                        <i class="fa-regular fa-eye"></i>
                                        View Jobs
                                    </a>
                                    <button type="button"
                                        class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                        <i class="fa-regular fa-trash-can"></i>
                                        Delete Jobs
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-sm text-center text-gray-500">No OV jobs available yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('pages.jobs.import-modal')
    </div>
</x-app-layout>
