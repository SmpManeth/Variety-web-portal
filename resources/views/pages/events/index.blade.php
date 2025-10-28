<x-app-layout>
    <div class="max-w-7xl mx-auto p-6" x-data="eventIndex()">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Events</h1>
                <p class="text-gray-500 text-sm">Manage and organize charity events for children</p>
            </div>

            <a href="{{ route('events.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                <i class="fa-solid fa-plus"></i>
                Create Event
            </a>
        </div>

        <!-- Search -->
        <div class="mb-4 flex items-center gap-3">
            <div class="flex-1 relative">
                <input type="text" placeholder="Search events..." x-model="search"
                       class="w-full rounded-lg border-gray-300 pl-10 pr-3 py-2 text-sm focus:border-red-500 focus:ring-red-500" />
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400"></i>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex flex-wrap items-center gap-3 mb-6">
            <button @click="tab='active'"
                    :class="tab==='active' ? 'bg-red-100 text-red-600' : 'bg-white text-gray-700 border'"
                    class="rounded-full px-4 py-1.5 text-sm font-medium border border-gray-200">Active Events</button>

            <button @click="tab='archived'"
                    :class="tab==='archived' ? 'bg-red-100 text-red-600' : 'bg-white text-gray-700 border'"
                    class="rounded-full px-4 py-1.5 text-sm font-medium border border-gray-200">Archived Events</button>
        </div>

        <!-- Status Filter -->
        <div class="flex flex-wrap gap-2 mb-8">
            <template x-for="st in statuses" :key="st">
                <button @click="filterStatus=st"
                        :class="filterStatus===st ? 'bg-red-600 text-white' : 'bg-white text-gray-700 border'"
                        class="rounded-full border border-gray-300 px-3 py-1.5 text-sm font-medium hover:bg-red-50">
                    <span x-text="st"></span>
                </button>
            </template>
        </div>

        <!-- Event Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $event)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm"
                     x-show="matches('{{ strtolower($event->title) }}', '{{ strtolower($event->description) }}', '{{ $event->status ?? 'active' }}')">
                    <!-- Status Badge -->
                    <div class="flex justify-between items-start mb-3">
                        <span class="rounded-full bg-red-100 text-red-700 text-xs font-medium px-3 py-1 capitalize">
                            {{ $event->status ?? 'active' }}
                        </span>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="font-semibold text-gray-900 text-lg mb-1">{{ $event->title }}</h3>
                    <p class="text-sm text-gray-600 mb-3 line-clamp-3">{{ $event->description }}</p>

                    <!-- Details -->
                    <ul class="text-sm text-gray-700 space-y-1 mb-4">
                        <li class="flex items-center gap-2">
                            <i class="fa-regular fa-calendar text-red-500"></i>
                            <span>{{ \Carbon\Carbon::parse($event->start_date)->format('d/m/Y') }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-regular fa-clock text-red-500"></i>
                            <span>
                                @php
                                    $days = \Carbon\Carbon::parse($event->start_date)->diffInDays(\Carbon\Carbon::parse($event->end_date)) + 1;
                                @endphp
                                {{ $days }} {{ Str::plural('day', $days) }}
                            </span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-users text-red-500"></i>
                            <span>{{ rand(0, $event->max_participants) }}/{{ $event->max_participants }} participants</span>
                        </li>
                    </ul>

                    <!-- Progress Bar -->
                    @php
                        $count = rand(0, $event->max_participants);
                        $percent = $event->max_participants > 0 ? ($count / $event->max_participants) * 100 : 0;
                    @endphp
                    <div class="h-2 rounded-full bg-red-100 mb-4">
                        <div class="h-2 rounded-full bg-red-500" style="width: {{ $percent }}%"></div>
                    </div>

                    <!-- Organizer -->
                    <p class="text-xs text-gray-500 mb-4">Organized by: <span class="font-medium">Sarah Mitchell</span></p>

                    <!-- Actions -->
                    <div class="flex justify-between items-center">
                        <div class="flex gap-2">
                            <a href="{{ route('events.show', $event) }}"
                               class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                <i class="fa-regular fa-eye"></i> View
                            </a>
                            <a href="{{ route('events.edit', $event) }}"
                               class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>
                        </div>

                        <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Delete this event?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                <i class="fa-regular fa-trash-can"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        function eventIndex() {
            return {
                search: '',
                tab: 'active',
                filterStatus: 'All',
                statuses: ['All','Active','Draft','Completed','Cancelled'],

                matches(title, desc, status) {
                    const term = this.search.toLowerCase();
                    const textMatch = title.includes(term) || desc.includes(term);
                    const statusMatch = this.filterStatus === 'All' ||
                        status.toLowerCase() === this.filterStatus.toLowerCase();
                    return textMatch && statusMatch;
                }
            }
        }
    </script>
</x-app-layout>
