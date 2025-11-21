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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                </div>
                                <div class="mt-4 space-y-4">
                                    <div class="space-y-2">
                                        <input :name="`days[${i}][itinerary_title]`" placeholder="Section title" x-model="day.itinerary_title"
                                               class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"/>
                                        <div class="gap-2">
                                            <div
                                                x-bind:data-name="`days[${i}][itinerary_description]`"
                                                x-bind:data-id="`days[${i}][itinerary_description]`"
                                                x-bind:data-value="day.itinerary_description"
                                            >                                                
                                                <x-trix-input-alpine
                                                    placeholder="Section description"
                                                />
                                            </div>
                                        </div>
                                    </div>
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
                <div class="w-1/3 justify-center" x-data="{
                    sponsorImageFile: null,
                    defaultPreview: '{{$event->sponsor_image_path ? '/storage/' . $event->sponsor_image_path : null}}',
                }">
                    <label for="sponsor_image">
                        {{-- Preview Section --}}
                        <div class="cursor-pointer bg-gray-100 rounded-lg aspect-square relative">
                            <button
                                x-show="sponsorImageFile"
                                type="button"
                                class="w-8 h-8 bg-red-600 rounded-full text-white absolute -right-2 -top-2"
                                @click="$refs.sponsorImageInput.value = ''; sponsorImageFile = null"
                            >X</button>
                            <img
                                class="object-contain w-full h-full rounded-lg"
                                x-bind:src="sponsorImageFile ? URL.createObjectURL(sponsorImageFile) : defaultPreview"
                                x-show="sponsorImageFile ?? defaultPreview"
                            />
                            <div x-show="!sponsorImageFile && !defaultPreview" class="w-full h-full flex flex-col items-center justify-center">
                                <img src="/images/icons/icons8-image-64.png" alt="">
                                <p class="mt-6">Drag and drop or <span class="font-bold">browse</span> files</p>
                                <p class="text-gray-500 text-sm">PNG, JPEG or JPG</p>
                            </div>
                        </div>

                        {{-- Button --}}
                        <div type="button" class="mt-2 text-center cursor-pointer rounded-lg bg-white border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                            Upload Image
                        </div>
                    </label>

                    {{-- Sponsor Image Input --}}
                    <input
                        type="file"
                        x-ref="sponsorImageInput"
                        name="sponsor_image"
                        id="sponsor_image"
                        class="hidden"
                        accept="image/*" 
                        @change="sponsorImageFile = $event.target.files[0];"
                    />
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
            }
        }
    </script>
</x-app-layout>
