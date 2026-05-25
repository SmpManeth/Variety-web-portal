<x-app-layout>
    <div class="max-w-7xl mx-auto p-6"
         x-data="{
             searchQuery: '',
             visibleCount: {{ $records->count() }},
             searchNorm() {
                 return (this.searchQuery || '').trim().toLowerCase();
             },
             rowVisible(tr) {
                 const q = this.searchNorm();
                 if (!q) return true;
                 const haystack = tr?.dataset?.search || '';
                 return haystack.includes(q);
             },
             updateFilterState() {
                 const rows = Array.from(this.$root.querySelectorAll('tr[data-record-row]'));
                 this.visibleCount = rows.filter((tr) => this.rowVisible(tr)).length;
             }
         }"
         x-init="updateFilterState()">

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

        <div class="items-center justify-between mb-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('medical-records.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                    <i class="fa-solid fa-chevron-left"></i>
                    Back to all Medical Records
                </a>

                <div class="flex gap-2">
                    <a href="{{ route('events.show', $event) }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-eye"></i> View Event
                    </a>
                    <form action="{{ route('medical-records.destroy', $event) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to delete these records?');">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
                            <i class="fa-solid fa-trash"></i> Delete Records
                        </button>
                    </form>
                </div>
            </div>
            <div class="mt-2">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Medical Records</h1>
                <p class="text-gray-500 text-sm">For {{ $event->title }} participants</p>
            </div>
        </div>

        <div class="mb-4 text-sm text-black flex gap-4">
            <div class="mb-1">
                <span class="font-semibold">Import Date:</span>
                <span>{{ $records->first()?->imported_at->format('d/m/Y') }}</span>
            </div>
            <div>
                <span class="font-semibold">Destroy Date:</span>
                <span>{{ $records->first()?->expires_at->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="mb-4 flex items-center gap-3">
            <div class="flex-1 relative">
                <input type="search"
                       placeholder="Search by name or vehicle..."
                       x-model="searchQuery"
                       @input="updateFilterState()"
                       class="w-full rounded-lg border-gray-300 pl-10 pr-3 py-2 text-sm focus:border-red-500 focus:ring-red-500" />
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400"></i>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
            <table class="w-full table-fixed text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-gray-600">
                        <th class="px-4 py-3 font-medium w-[22%]">First name</th>
                        <th class="px-4 py-3 font-medium w-[22%]">Last name</th>
                        <th class="px-4 py-3 font-semibold text-gray-900 w-[36%]">Vehicle</th>
                        <th class="px-4 py-3 font-medium text-right w-[20%]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($records as $record)
                        @php
                            $content = is_string($record->content)
                                    ? json_decode($record->content)
                                    : $record->content;

                            // Create a unique, condensed lowercase search string for the row
                            $searchHaystack = \Illuminate\Support\Str::lower(implode(' ', array_filter([
                                $content->first_name ?? '',
                                $content->last_name ?? '',
                                $content->vehicle ?? ''
                            ])));
                        @endphp

                        <tr class="hover:bg-gray-50"
                            data-record-row
                            data-search="{{ e($searchHaystack) }}"
                            x-show="rowVisible($el)"
                            x-cloak>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $content->first_name ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $content->last_name ?? '—' }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $content->vehicle ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <a href="{{ route('medical-records.show-record', [$event, $record]) }}" class="inline-flex items-center rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">No records found.</td>
                        </tr>
                    @endforelse

                    @if($records->isNotEmpty())
                        <tr x-show="searchNorm() && visibleCount === 0" x-cloak>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                No records match your search.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
