<x-app-layout>
    <div class="max-w-7xl mx-auto p-6"
         x-data="eventShow({ days: {{ $daysJson }} })">

        <!-- Top: Back + Title + Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('events.index') }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                <i class="fa-solid fa-chevron-left"></i>
                Back to Events
            </a>

            <div class="flex items-center gap-2">
                <a href="#" class="hidden md:inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                    <i class="fa-solid fa-users"></i> Manage Participants
                </a>
                <a href="{{ route('events.edit', $event) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-pen"></i> Edit Event
                </a>
            </div>
        </div>

        <!-- Title + subtitle -->
        <div class="mt-2">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ $event->title }}</h1>
            <p class="text-gray-500 text-sm">{{ \Illuminate\Support\Str::limit($event->description, 180) }}</p>
        </div>

        <!-- Summary Card -->
        <div class="mt-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">active</span>
                        <span class="text-xs text-gray-400">event</span>
                    </div>
                    <h2 class="mt-2 text-lg font-semibold text-gray-900">{{ $event->title }}</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 w-full md:w-auto">
                    <!-- Start Date -->
                    <div class="flex items-start gap-3">
                        <i class="fa-regular fa-calendar text-red-600 mt-0.5"></i>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Start Date</p>
                            <p class="text-sm text-gray-800">
                                {{ $event->start_date->translatedFormat('l d F Y') }}
                            </p>
                        </div>
                    </div>

                    <!-- Duration -->
                    <div class="flex items-start gap-3">
                        <i class="fa-regular fa-clock text-red-600 mt-0.5"></i>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Duration</p>
                            <p class="text-sm text-gray-800">
                                {{ $durationDays }} {{ \Illuminate\Support\Str::plural('day', $durationDays) }}
                                <span class="text-gray-400">•</span>
                                {{ $event->start_date->format('d/m/Y') }}
                                – {{ $event->end_date->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>

                    <!-- Participants -->
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-user-group text-red-600 mt-0.5"></i>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Participants</p>
                            <p class="text-sm text-gray-800">
                                2 / {{ $event->max_participants }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer meta -->
            <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-gray-500">
                <span>Organized by: <span class="font-medium text-gray-700">James Wilson</span></span>
                <span>Event duration: {{ $durationDays }} {{ \Illuminate\Support\Str::plural('day', $durationDays) }}</span>
            </div>
        </div>

        <!-- Main Layout -->
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Itinerary List -->
            <aside class="lg:col-span-4">
                <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                    <div class="flex items-center gap-2 px-4 py-3 border-b border-gray-200">
                        <i class="fa-regular fa-clipboard text-gray-700"></i>
                        <h3 class="text-sm font-semibold text-gray-900">Itinerary</h3>
                    </div>

                    <div class="p-3 space-y-2">
                        <template x-if="days.length === 0">
                            <p class="text-sm text-gray-500 px-2 py-3">No itinerary added yet.</p>
                        </template>

                        <template x-for="(d, idx) in days" :key="idx">
                            <button type="button"
                                    @click="select(idx)"
                                    class="w-full text-left rounded-lg px-3 py-3 border transition"
                                    :class="selected === idx ? 'border-red-200 bg-red-50' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900" x-text="d.title"></p>
                                        <p class="text-xs text-gray-500" x-text="d.date_short ?? ''"></p>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-xs text-gray-400"></i>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
            </aside>

            <!-- Right: Selected Day Content -->
            <section class="lg:col-span-8 space-y-6">
                <!-- Day header, image, subtitle -->
                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <div class="mb-3">
                        <h4 class="text-sm font-semibold text-gray-900" x-text="current.title || 'Select a day'"></h4>
                        <p class="text-xs text-gray-500" x-text="current.date || ''"></p>
                    </div>

                    <template x-if="current.image">
                        <img :src="current.image" alt=""
                             class="w-full h-56 md:h-64 rounded-lg object-cover">
                    </template>

                    <template x-if="!current.image">
                        <div class="w-full h-40 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                            <i class="fa-regular fa-image text-3xl"></i>
                        </div>
                    </template>

                    <template x-if="current.subtitle">
                        <p class="mt-3 text-sm text-gray-600" x-text="current.subtitle"></p>
                    </template>
                </div>

                <!-- Key Locations -->
                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fa-regular fa-compass text-gray-700"></i>
                        <h4 class="text-sm font-semibold text-gray-900">Key Locations</h4>
                    </div>

                    <template x-if="!current.locations || current.locations.length === 0">
                        <p class="text-sm text-gray-500">No locations added.</p>
                    </template>

                    <template x-for="loc in current.locations" :key="loc.name">
                        <div class="flex items-center justify-between rounded-lg bg-red-50/60 px-3 py-2 mb-2">
                            <span class="text-sm text-gray-800" x-text="loc.name"></span>
                            <template x-if="loc.link_url && loc.link_title">
                                <a :href="loc.link_url" target="_blank"
                                   class="inline-flex items-center gap-1 rounded-full border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                    <i class="fa-solid fa-up-right-from-square text-[11px]"></i>
                                    <span x-text="loc.link_title"></span>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Event Details -->
                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Event Details</h4>
                    <template x-if="!current.details || current.details.length === 0">
                        <p class="text-sm text-gray-500">No details added.</p>
                    </template>

                    <template x-for="det in current.details" :key="det.title + det.description">
                        <div class="mb-4">
                            <p class="text-xs font-semibold text-gray-700" x-text="det.title"></p>
                            <p class="text-sm text-gray-700" x-text="det.description"></p>
                        </div>
                    </template>
                </div>

                <!-- Additional Resources -->
                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Additional Resources</h4>

                    <template x-if="!current.resources || current.resources.length === 0">
                        <p class="text-sm text-gray-500">No resources added.</p>
                    </template>

                    <div class="flex flex-wrap gap-3">
                        <template x-for="res in current.resources" :key="res.title + res.url">
                            <a :href="res.url" target="_blank"
                               class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                <i class="fa-solid fa-up-right-from-square text-[11px]"></i>
                                <span x-text="res.title"></span>
                            </a>
                        </template>
                    </div>
                </div>
            </section>
        </div>

        <!-- Sponsors -->
        <section class="mt-6 rounded-xl border border-gray-200 bg-white p-5">
            <h4 class="text-sm font-semibold text-gray-900">Event Sponsors</h4>
            <p class="text-xs text-gray-500 mb-4">Thank you to our generous sponsors who make this event possible.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @forelse ($event->sponsors as $s)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 text-center">
                        <div class="mx-auto h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center">
                            @if($s->logo_url)
                                <img src="{{ $s->logo_url }}" alt="{{ $s->name }}" class="h-10 object-contain">
                            @else
                                <i class="fa-regular fa-image text-gray-400"></i>
                            @endif
                        </div>
                        <p class="mt-3 text-xs font-semibold text-gray-800">{{ $s->name }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No sponsors added.</p>
                @endforelse
            </div>
        </section>
    </div>

    <script>
        function eventShow({ days }) {
            return {
                days: days || [],
                selected: 0,
                get current() { return this.days[this.selected] || {}; },
                select(idx) {
                    this.selected = idx;
                    if (window.innerWidth < 1024) {
                        setTimeout(() => {
                            document.querySelector('section.lg\\:col-span-8')?.scrollIntoView({
                                behavior: 'smooth', block: 'start'
                            });
                        }, 0);
                    }
                }
            }
        }
    </script>
</x-app-layout>
