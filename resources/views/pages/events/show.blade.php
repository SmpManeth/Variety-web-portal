<x-app-layout>
    <div class="max-w-7xl mx-auto p-6"
        x-data="{
            days: {{ $daysJson }},
            participants: {{ $participantsJson }},
            selected: 0,
            showParticipants: localStorage.getItem('showParticipants') === 'true',
            openModal: false,
            modalType: null,
            modalData: {},
            errors: {},
            selectedParticipantIds: [],
            participantSearch: '',
            submitting: false,

            get current() {
                return this.days[this.selected] || {};
            },

            toggleParticipants() {
                this.showParticipants = !this.showParticipants;
                localStorage.setItem('showParticipants', this.showParticipants);
            },

            select(idx) {
                this.selected = idx;
                if (window.innerWidth < 1024) {
                    setTimeout(() => {
                        document.querySelector('section.lg\\:col-span-8')?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }, 0);
                }
            },

            get filteredParticipants() {
                const query = this.participantSearch.trim().toLowerCase();
                if (!query) return this.participants;
                return this.participants.filter(p => {
                    return [
                        p.first_name, p.last_name, p.email, p.phone, p.vehicle,
                        p.emergency_contact_name, p.emergency_contact_relationship,
                        p.emergency_contact_phone, p.role_names, p.status
                    ].filter(Boolean).join(' ').toLowerCase().includes(query);
                });
            },

            openAddModal() {
                this.modalType = 'create';
                this.errors = {};
                this.modalData = {
                    id: '', first_name: '', last_name: '', email: '', phone: '', vehicle: '',
                    status: 'active', emergency_contact_name: '', emergency_contact_phone: '',
                    emergency_contact_relationship: '', roles: []
                };
                this.openModal = true;
            },

            openEditModal(p) {
                this.modalType = 'edit';
                this.errors = {};
                const roleIds = p.roles ? p.roles.map(r => r.id || r) : (p.role_ids || []);
                this.modalData = JSON.parse(JSON.stringify({ ...p, roles: roleIds }));
                this.openModal = true;
            },

            async submitForm(e) {
                const isEdit = this.modalType === 'edit';
                const url = isEdit
                    ? `/events/{{ $event->id }}/participants/${this.modalData.id}`
                    : `{{ route('participants.store', $event) }}`;

                const method = isEdit ? 'PUT' : 'POST';

                // Prevent passing empty-string IDs down to the database row processors
                let payload = { ...this.modalData };
                if (!isEdit) {
                    delete payload.id;
                }

                this.submitting = true;
                this.errors = {};

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: method,
                            ...payload
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422) {
                            this.errors = data.errors;
                        } else {
                            alert(data.message || 'An error occurred.');
                        }
                        return;
                    }

                    if (data.success) {
                        window.location.href = data.redirect || '{{ url()->current() }}';
                    }
                } catch (err) {
                    console.error(err);
                    alert('A network error occurred.');
                } finally {
                    this.submitting = false;
                }
            },

            async deleteParticipant(pId) {
                if (!confirm('Are you sure you want to delete this participant?')) return;

                this.submitting = true;

                try {
                    const response = await fetch(`/events/{{ $event->id }}/participants/${pId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    });

                    if (response.ok) {
                        this.participants = this.participants.filter(p => p.id !== pId);
                    } else {
                        const data = await response.json();
                        alert(data.message || 'Could not delete participant.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('A network error occurred.');
                } finally {
                    this.submitting = false;
                }
            }
        }">

        <div class="flex items-center justify-between">
            <a href="{{ route('events.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                <i class="fa-solid fa-chevron-left"></i>
                Back to Events
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('events.forms.index', $event) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-file-alt"></i>
                    <span>Forms</span>
                </a>

                <a href="{{ route('events.permits.index', $event) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-id-card"></i>
                    <span>Permits</span>
                </a>

                <a href="#"
                    @click.prevent="toggleParticipants()"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                    <i class="fa-solid fa-users"></i>
                    <span x-text="showParticipants ? 'Hide Participants' : 'View Participants'"></span>
                </a>

                <a href="{{ route('events.passwords.index', $event) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-asterisk"></i>
                    <span>Passwords</span>
                </a>

                @can('SUPERADMIN')
                <a href="{{ route('events.admins.index', $event) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-user-tie"></i>
                    <span>Admins</span>
                </a>
                @endcan

                <a href="{{ route('events.edit', $event) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-pen"></i> Edit Event
                </a>
            </div>
        </div>

        <div class="mt-2">
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ $event->title }}</h1>
            <p class="text-gray-500 text-sm">{{ \Illuminate\Support\Str::limit($event->description, 180) }}</p>
        </div>

        @if ($event->cover_image_path)
        <div class="mt-4">
            <img src="/storage/{{ $event->cover_image_path }}" class="w-full h-32 md:h-64 object-cover rounded-xl border border-gray-200 shadow-sm">
        </div>
        @endif

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
                    <div class="flex items-start gap-3">
                        <i class="fa-regular fa-calendar text-red-600 mt-0.5"></i>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Start Date</p>
                            <p class="text-sm text-gray-800">
                                {{ $event->start_date->translatedFormat('l d F Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <i class="fa-regular fa-clock text-red-600 mt-0.5"></i>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Duration</p>
                            <p class="text-sm text-gray-800">
                                {{ $durationDays }} {{ \Illuminate\Support\Str::plural('day', $durationDays) }}
                                <span class="text-gray-400">•</span>
                                {{ $event->start_date->format('d/m/Y') }} – {{ $event->end_date->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-user-group text-red-600 mt-0.5"></i>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Participants</p>
                            <p class="text-sm text-gray-800" x-text="participants.length"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-gray-500">
                <span>Organized by: <span class="font-medium text-gray-700">{{ $event->organizerDisplayName() ?: '—' }}</span></span>
                <span>Event duration: {{ $durationDays }} {{ \Illuminate\Support\Str::plural('day', $durationDays) }}</span>
            </div>
        </div>

        <section id="participants-section" x-show="showParticipants" x-transition class="mt-6 space-y-6" x-cloak>
            @php
                $participantTableColspan = auth()->user()->can('manage participants') ? 8 : 7;
            @endphp
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold flex items-center gap-2 text-gray-900">
                        <i class="fa-solid fa-user-group text-red-600"></i>
                        Event Participants (<span x-text="participants.length"></span>)
                    </h2>

                    @can('manage participants')
                    <div class="flex gap-3">
                        <form method="POST" action="{{ route('participants.bulkDestroy', $event) }}" class="flex items-center gap-2"
                            x-show="selectedParticipantIds.length > 0"
                            x-cloak
                            onsubmit="return confirm('Delete selected participants?')">
                            @csrf
                            @csrf
                            @method('DELETE')
                            <template x-for="id in selectedParticipantIds" :key="id">
                                <input type="hidden" name="participant_ids[]" :value="id">
                            </template>
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                <i class="fa-solid fa-trash"></i>
                                Delete selected (<span x-text="selectedParticipantIds.length"></span>)
                            </button>
                        </form>

                        <form method="POST" action="{{ route('participants.import', $event) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                            @csrf
                            <input type="file" name="file" accept=".xlsx,.xls" id="excelImport" class="hidden" onchange="this.form.submit()">
                            <button type="button" onclick="document.getElementById('excelImport').click()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">
                                <i class="fa-solid fa-upload"></i> Import Excel
                            </button>
                        </form>

                        <a href="{{ route('participants.template') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">
                            <i class="fa-solid fa-file-arrow-down"></i> Download Template
                        </a>

                        <button @click="openAddModal()" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            <i class="fa-solid fa-user-plus"></i> Add Participant
                        </button>
                    </div>
                    @endcan
                </div>

                <div class="mb-4">
                    <label for="participant-search" class="block text-xs font-semibold text-gray-600 mb-1">Search participants</label>
                    <input
                        id="participant-search" type="search" placeholder="Name, email, phone, vehicle, roles…"
                        x-model="participantSearch"
                        class="w-full rounded-lg border border-gray-300 bg-white py-2 px-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"
                    >
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-t">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-gray-600">
                                @can('manage participants')
                                <th class="px-4 py-2 font-medium w-10">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                        @change="if ($event.target.checked) { selectedParticipantIds = filteredParticipants.map(p => p.id) } else { selectedParticipantIds = [] }"
                                        :checked="filteredParticipants.length > 0 && selectedParticipantIds.length === filteredParticipants.length">
                                </th>
                                @endcan
                                <th class="px-4 py-2 font-medium">Name</th>
                                <th class="px-4 py-2 font-medium">Vehicle</th>
                                <th class="px-4 py-2 font-medium">Contact</th>
                                <th class="px-4 py-2 font-medium">Emergency Contact</th>
                                <th class="px-4 py-2 font-medium">Roles</th>
                                <th class="px-4 py-2 font-medium">Status</th>
                                <th class="px-4 py-2 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-if="participants.length === 0">
                                <tr>
                                    <td colspan="{{ $participantTableColspan }}" class="px-4 py-6 text-center text-gray-500">No participants yet.</td>
                                </tr>
                            </template>

                            <template x-if="participants.length > 0 && filteredParticipants.length === 0">
                                <tr>
                                    <td colspan="{{ $participantTableColspan }}" class="px-4 py-6 text-center text-gray-500">
                                        No participants match your search criteria.
                                    </td>
                                </tr>
                            </template>

                            <template x-for="p in filteredParticipants" :key="p.id">
                                <tr class="hover:bg-gray-50/50 transition duration-150">
                                    @can('manage participants')
                                    <td class="px-4 py-2">
                                        <input type="checkbox"
                                            :value="p.id"
                                            class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                            x-model="selectedParticipantIds">
                                    </td>
                                    @endcan

                                    <td class="px-4 py-2 font-semibold text-gray-900">
                                        <span x-text="(p.first_name + ' ' + (p.last_name || '')).trim()"></span>
                                        <div class="text-xs text-gray-500 font-normal" x-text="p.email || '—'"></div>
                                    </td>

                                    <td class="px-4 py-2 text-gray-600" x-text="p.vehicle || '—'"></td>
                                    <td class="px-4 py-2 text-gray-600" x-text="p.phone || '—'"></td>

                                    <td class="px-4 py-2">
                                        <span class="text-gray-800" x-text="p.emergency_contact_name || '—'"></span>
                                        <div class="text-xs text-gray-500" x-text="p.emergency_contact_relationship || ''"></div>
                                    </td>

                                    <td class="px-4 py-2 text-gray-600" x-text="p.role_names || '—'"></td>

                                    <td class="px-4 py-2">
                                        <span class="px-2 py-0.5 text-xs rounded-full font-medium"
                                              :class="p.status === 'active' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'"
                                              x-text="p.status">
                                        </span>
                                    </td>

                                    <td class="px-4 py-2 text-right">
                                        @can('manage participants')
                                        <div class="flex justify-end gap-3">
                                            <button type="button"
                                                @click="openEditModal(p)"
                                                class="text-gray-500 hover:text-gray-800 transition">
                                                <i class="fa-solid fa-pen text-sm"></i>
                                            </button>

                                            <button type="button"
                                                @click="deleteParticipant(p.id)"
                                                class="text-red-500 hover:text-red-700 transition">
                                                <i class="fa-solid fa-trash text-sm"></i>
                                            </button>
                                        </div>
                                        @else
                                        <span class="text-gray-400">—</span>
                                        @endcan
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="openModal" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
                <div @click.outside="openModal = false" class="bg-white rounded-xl w-full max-w-2xl p-6">
                    @include('pages.events.participants.manage-form', ['event' => $event])
                </div>
            </div>
        </section>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
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

            <section class="lg:col-span-8 space-y-6">
                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <div class="mb-3">
                        <h4 class="text-sm font-semibold text-gray-900" x-text="current.title || 'Select a day'"></h4>
                        <p class="text-xs text-gray-500" x-text="current.date || ''"></p>
                    </div>

                    <template x-if="current.image">
                        <img :src="`/storage/${current.image}`" alt="" class="w-full h-56 md:h-64 rounded-lg object-cover">
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

                <div class="rounded-xl border border-gray-200 bg-white p-5">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Itinerary Details</h4>
                    <p class="text-lg font-semibold text-gray-700 mb-4" x-text="current.itinerary_title"></p>
                    <div class="text-sm text-gray-700" x-html="current.itinerary_description"></div>
                </div>

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

        <section class="mt-6 rounded-xl border border-gray-200 bg-white p-5">
            <h4 class="text-sm font-semibold text-gray-900">Event Sponsors</h4>
            <p class="text-xs text-gray-500 mb-4">Thank you to our generous sponsors who make this event possible.</p>
            <div>
                @if ($event->sponsor_image_path)
                    <img src="/storage/{{ $event->sponsor_image_path }}" class="w-full md:w-1/3 rounded-lg">
                @else
                <p class="text-sm text-gray-500">No sponsors added.</p>
                @endif
            </div>
        </section>
    </div>
</x-app-layout>
