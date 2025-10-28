<x-app-layout>
    <div class="max-w-6xl mx-auto p-6">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition
                 class="mb-4 flex items-center justify-between rounded-md bg-green-50 p-3 text-green-800 border border-green-200">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-green-700 hover:text-green-900">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-4 text-red-800">
                <div class="flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation mt-1"></i>
                    <div>
                        <p class="font-semibold mb-1">Please correct the following errors:</p>
                        <ul class="list-disc list-inside text-sm space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form x-data="editForm({
                    initDays: @js($daysJson),
                    initSponsors: @js($sponsorsJson),
                    old: @js(old())
               })"
              x-init="init()"
              method="POST"
              action="{{ route('events.update', $event) }}"
              enctype="multipart/form-data"
              class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Event Info -->
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fa-regular fa-calendar text-gray-700 text-lg"></i>
                    <h2 class="text-xl font-semibold text-gray-900">Edit Event</h2>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Event Title *</label>
                        <input name="title" type="text" value="{{ old('title', $event->title) }}" required
                               class="mt-1 w-full rounded-lg border {{ $errors->has('title') ? 'border-red-500' : 'border-gray-300' }} focus:ring-red-500 focus:border-red-500"
                               placeholder="Enter event title" />
                        @error('title') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description *</label>
                        <textarea name="description" rows="5" required
                                  class="mt-1 w-full rounded-lg border {{ $errors->has('description') ? 'border-red-500' : 'border-gray-300' }} focus:ring-red-500 focus:border-red-500"
                                  placeholder="Describe the event">{{ old('description', $event->description) }}</textarea>
                        @error('description') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date *</label>
                            <input name="start_date" type="date" value="{{ old('start_date', $event->start_date->format('Y-m-d')) }}" required
                                   class="mt-1 w-full rounded-lg border {{ $errors->has('start_date') ? 'border-red-500' : 'border-gray-300' }} focus:ring-red-500 focus:border-red-500" />
                            @error('start_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date *</label>
                            <input name="end_date" type="date" value="{{ old('end_date', $event->end_date->format('Y-m-d')) }}" required
                                   class="mt-1 w-full rounded-lg border {{ $errors->has('end_date') ? 'border-red-500' : 'border-gray-300' }} focus:ring-red-500 focus:border-red-500" />
                            @error('end_date') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Max Participants *</label>
                            <input name="max_participants" type="number" min="1" value="{{ old('max_participants', $event->max_participants) }}" required
                                   class="mt-1 w-full rounded-lg border {{ $errors->has('max_participants') ? 'border-red-500' : 'border-gray-300' }} focus:ring-red-500 focus:border-red-500" />
                            @error('max_participants') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </section>

            <!-- Itinerary -->
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 pt-6">
                    <h2 class="text-xl font-semibold text-gray-900">Itinerary</h2>
                    <button type="button" @click="addDay()"
                            class="inline-flex items-center gap-2 rounded-lg bg-white border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                        <i class="fa-solid fa-plus"></i> Add Day
                    </button>
                </div>

                <div class="p-6 space-y-8">
                    <template x-for="(day, i) in days" :key="i">
                        <div class="rounded-xl border border-gray-200 p-5 bg-white">
                            <div class="flex items-start justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Day <span x-text="i + 1"></span></h3>
                                <button type="button" @click="removeDay(i)" class="text-sm text-red-600 hover:underline">Remove Day</button>
                            </div>

                            <!-- Hidden ID (existing days) -->
                            <template x-if="day.id">
                                <input type="hidden" :name="`days[${i}][id]`" x-model="day.id">
                            </template>
                            <input type="hidden" :name="`days[${i}][sort_order]`" :value="i">

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Day Title *</label>
                                    <input :name="`days[${i}][title]`" x-model="day.title" required
                                           class="mt-1 w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date *</label>
                                    <input :name="`days[${i}][date]`" x-model="day.date" type="date" required
                                           class="mt-1 w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Subtitle</label>
                                    <input :name="`days[${i}][subtitle]`" x-model="day.subtitle"
                                           class="mt-1 w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                </div>

                                <!-- Image -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Day Image</label>

                                    <template x-if="day.image_url">
                                        <div class="mb-2 flex items-center gap-3">
                                            <img :src="day.image_url" class="h-20 w-32 rounded-lg object-cover border">
                                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                <input type="checkbox" :name="`days[${i}][remove_image]`" x-model="day.remove_image" value="1" class="rounded">
                                                Remove current image
                                            </label>
                                        </div>
                                    </template>

                                    <input :name="`days[${i}][image]`" type="file"
                                           class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-red-600 file:px-4 file:py-2 file:font-medium file:text-white hover:file:bg-red-700"/>
                                    <p class="text-xs text-gray-500 mt-1">Optional. JPG/PNG up to 4MB.</p>
                                </div>
                            </div>

                            <!-- Locations -->
                            <div class="mt-6 rounded-xl border border-gray-100 bg-red-50/40 p-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-semibold text-gray-900">Key Locations</h4>
                                    <button type="button" @click="addLocation(i)"
                                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                                        <i class="fa-solid fa-plus"></i> Add Location
                                    </button>
                                </div>
                                <div class="mt-4 space-y-4">
                                    <template x-for="(loc, j) in day.locations" :key="j">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <template x-if="loc.id">
                                                <input type="hidden" :name="`days[${i}][locations][${j}][id]`" x-model="loc.id">
                                            </template>
                                            <input :name="`days[${i}][locations][${j}][name]`" x-model="loc.name" placeholder="Location name"
                                                   class="rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                            <input :name="`days[${i}][locations][${j}][link_title]`" x-model="loc.link_title" placeholder="Link title"
                                                   class="rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                            <div class="flex gap-2">
                                                <input :name="`days[${i}][locations][${j}][link_url]`" x-model="loc.link_url" placeholder="Link URL"
                                                       class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                                <input type="hidden" :name="`days[${i}][locations][${j}][sort_order]`" :value="j">
                                                <button type="button" @click="removeLocation(i,j)"
                                                        class="rounded-lg border border-gray-200 px-3 text-sm hover:bg-gray-50">Remove</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="mt-6 rounded-xl border border-gray-100 bg-red-50/40 p-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-semibold text-gray-900">Itinerary Details</h4>
                                    <button type="button" @click="addDetail(i)"
                                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                                        <i class="fa-solid fa-plus"></i> Add Detail
                                    </button>
                                </div>
                                <div class="mt-4 space-y-4">
                                    <template x-for="(det, k) in day.details" :key="k">
                                        <div class="space-y-2">
                                            <template x-if="det.id">
                                                <input type="hidden" :name="`days[${i}][details][${k}][id]`" x-model="det.id">
                                            </template>
                                            <input :name="`days[${i}][details][${k}][title]`" x-model="det.title" placeholder="Section title"
                                                   class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                            <div class="flex gap-2">
                                                <textarea :name="`days[${i}][details][${k}][description]`" x-model="det.description" rows="3" placeholder="Description"
                                                          class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"></textarea>
                                                <input type="hidden" :name="`days[${i}][details][${k}][sort_order]`" :value="k">
                                                <button type="button" @click="removeDetail(i,k)"
                                                        class="h-10 self-start rounded-lg border border-gray-200 px-3 text-sm hover:bg-gray-50">Remove</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Resources -->
                            <div class="mt-6 rounded-xl border border-gray-100 bg-red-50/40 p-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-semibold text-gray-900">Additional Resources</h4>
                                    <button type="button" @click="addResource(i)"
                                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                                        <i class="fa-solid fa-plus"></i> Add Button
                                    </button>
                                </div>
                                <div class="mt-4 space-y-4">
                                    <template x-for="(res, r) in day.resources" :key="r">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <template x-if="res.id">
                                                <input type="hidden" :name="`days[${i}][resources][${r}][id]`" x-model="res.id">
                                            </template>
                                            <input :name="`days[${i}][resources][${r}][title]`" x-model="res.title" placeholder="Button title"
                                                   class="rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                            <div class="flex gap-2">
                                                <input :name="`days[${i}][resources][${r}][url]`" x-model="res.url" placeholder="Button link URL"
                                                       class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                                <input type="hidden" :name="`days[${i}][resources][${r}][sort_order]`" :value="r">
                                                <button type="button" @click="removeResource(i,r)"
                                                        class="rounded-lg border border-gray-200 px-3 text-sm hover:bg-gray-50">Remove</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="button" @click="moveDayUp(i)" class="mr-2 text-sm text-gray-600 hover:underline">Move Up</button>
                                <button type="button" @click="moveDayDown(i)" class="text-sm text-gray-600 hover:underline">Move Down</button>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            <!-- Sponsors -->
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Event Sponsors</h2>

                <div class="rounded-xl border border-gray-100 bg-red-50/40 p-4">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-900">Add New Sponsor</span>
                        <button type="button" @click="addSponsor()"
                                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                            <i class="fa-solid fa-plus"></i> Add
                        </button>
                    </div>

                    <div class="mt-4 space-y-3">
                        <template x-for="(sp, s) in sponsors" :key="s">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <template x-if="sp.id">
                                    <input type="hidden" :name="`sponsors[${s}][id]`" x-model="sp.id">
                                </template>
                                <input :name="`sponsors[${s}][name]`" x-model="sp.name" placeholder="Sponsor name"
                                       class="rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                <div class="flex md:col-span-2 gap-2">
                                    <input :name="`sponsors[${s}][logo_url]`" x-model="sp.logo_url" placeholder="Logo URL"
                                           class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                    <input type="hidden" :name="`sponsors[${s}][sort_order]`" :value="s">
                                    <button type="button" @click="removeSponsor(s)"
                                            class="rounded-lg border border-gray-200 px-3 text-sm hover:bg-gray-50">Remove</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <div class="flex justify-end gap-3">
                <a href="{{ route('events.show', $event) }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Update Event
                </button>
            </div>
        </form>
    </div>

    <script>
        function editForm({ initDays, initSponsors, old }) {
            return {
                days: [],
                sponsors: [],
                init() {
                    // If validation failed, prefer old() snapshot
                    if (old && old.days) {
                        this.days = old.days;
                    } else {
                        this.days = Array.isArray(initDays) ? initDays : JSON.parse(initDays || '[]');
                    }

                    if (old && old.sponsors) {
                        this.sponsors = old.sponsors;
                    } else {
                        this.sponsors = Array.isArray(initSponsors) ? initSponsors : JSON.parse(initSponsors || '[]');
                    }
                },

                // Days
                addDay() {
                    this.days.push({
                        id: null, title: '', date: '', subtitle: '',
                        image_url: null, remove_image: false,
                        locations: [], details: [], resources: []
                    });
                },
                removeDay(i) { this.days.splice(i, 1); },
                moveDayUp(i)   { if (i > 0) { const d = this.days.splice(i, 1)[0]; this.days.splice(i - 1, 0, d); } },
                moveDayDown(i) { if (i < this.days.length - 1) { const d = this.days.splice(i, 1)[0]; this.days.splice(i + 1, 0, d); } },

                // Locations
                addLocation(i) { this.days[i].locations.push({ id: null, name: '', link_title: '', link_url: '' }); },
                removeLocation(i, j) { this.days[i].locations.splice(j, 1); },

                // Details
                addDetail(i) { this.days[i].details.push({ id: null, title: '', description: '' }); },
                removeDetail(i, k) { this.days[i].details.splice(k, 1); },

                // Resources
                addResource(i) { this.days[i].resources.push({ id: null, title: '', url: '' }); },
                removeResource(i, r) { this.days[i].resources.splice(r, 1); },

                // Sponsors
                addSponsor() { this.sponsors.push({ id: null, name: '', logo_url: '' }); },
                removeSponsor(s) { this.sponsors.splice(s, 1); },
            }
        }
    </script>
</x-app-layout>
