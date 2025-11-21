<x-app-layout>
    <div class="max-w-6xl mx-auto p-6">
        <!-- ✅ Global Success Message -->
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition
                 class="mb-4 flex items-center justify-between rounded-md bg-green-50 p-3 text-green-800 border border-green-200">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-green-700 hover:text-green-900">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- ⚠️ Global Error Summary -->
        @if ($errors->any())
            <div class="mb-6 rounded-md bg-red-50 border border-red-200 p-4 text-red-800">
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

        <!-- 🎯 Main Event Form -->
        <form 
            x-data="eventForm()" 
            method="POST" 
            action="{{ route('events.store') }}" 
            enctype="multipart/form-data"
            @submit="loading = true"
            class="space-y-8"
        >
            @csrf

            <!-- Event Information -->
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2v-8H3v8a2 2 0 002 2z" />
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900">Event Information</h2>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Event Title *</label>
                        <input 
                            name="title" 
                            type="text" 
                            value="{{ old('title') }}"
                            required 
                            placeholder="Enter event title"
                            class="mt-1 w-full rounded-lg border {{ $errors->has('title') ? 'border-red-500' : 'border-gray-300' }} focus:border-red-500 focus:ring-red-500"
                        />
                        @error('title') 
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description *</label>
                        <textarea 
                            name="description" 
                            required 
                            rows="5" 
                            placeholder="Describe the event"
                            class="mt-1 w-full rounded-lg border {{ $errors->has('description') ? 'border-red-500' : 'border-gray-300' }} focus:border-red-500 focus:ring-red-500"
                        >{{ old('description') }}</textarea>
                        @error('description') 
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p> 
                        @enderror
                    </div>

                    <!-- Dates + Participants -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date *</label>
                            <input 
                                name="start_date" 
                                type="date" 
                                value="{{ old('start_date') }}"
                                required
                                class="mt-1 w-full rounded-lg border {{ $errors->has('start_date') ? 'border-red-500' : 'border-gray-300' }} focus:border-red-500 focus:ring-red-500"
                            />
                            @error('start_date') 
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p> 
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date *</label>
                            <input 
                                name="end_date" 
                                type="date" 
                                value="{{ old('end_date') }}"
                                required
                                class="mt-1 w-full rounded-lg border {{ $errors->has('end_date') ? 'border-red-500' : 'border-gray-300' }} focus:border-red-500 focus:ring-red-500"
                            />
                            @error('end_date') 
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p> 
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

              <!-- Event Itinerary -->
              <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 pt-6">
                    <h2 class="text-xl font-semibold text-gray-900">Event Itinerary</h2>
                    <button type="button" @click="addDay()"
                            class="inline-flex items-center gap-2 rounded-lg bg-white border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                        <span class="text-xl leading-none">＋</span> Add Day
                    </button>
                </div>

                <template x-if="days.length === 0">
                    <div class="p-10 text-center text-gray-500">
                        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2v-8H3v8a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2">No days added yet. Click “Add Day” to get started.</p>
                    </div>
                </template>

                <div class="p-6 space-y-8">
                    <template x-for="(day, i) in days" :key="i">
                        <div class="rounded-xl border border-gray-200 p-5 bg-white">
                            <!-- Day header -->
                            <div class="flex items-start justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Day Information</h3>
                                <button type="button" @click="removeDay(i)" class="text-sm text-red-600 hover:underline">Remove Day</button>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Day Title *</label>
                                    <input :name="`days[${i}][title]`" x-model="day.title" required
                                           placeholder="e.g., Welcome & Registration"
                                           class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date *</label>
                                    <input :name="`days[${i}][date]`" x-model="day.date" type="date" required
                                           class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"/>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Subtitle</label>
                                    <input :name="`days[${i}][subtitle]`" x-model="day.subtitle"
                                           placeholder="e.g., Grand opening with special performances"
                                           class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"/>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Day Image</label>
                                    <input :name="`days[${i}][image]`" type="file"
                                           class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-red-600 file:px-4 file:py-2 file:font-medium file:text-white hover:file:bg-red-700"/>
                                    <p class="text-xs text-gray-500 mt-1">Optional. JPG/PNG up to 4MB.</p>
                                </div>
                            </div>

                            <!-- Key Locations -->
                            <div class="mt-6 rounded-xl border border-gray-100 bg-red-50/40 p-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-semibold text-gray-900">Key Locations</h4>
                                    <button type="button" @click="addLocation(i)"
                                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                                        ＋ Add Location
                                    </button>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <template x-for="(loc, j) in day.locations" :key="j">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <input :name="`days[${i}][locations][${j}][name]`" x-model="loc.name" placeholder="Location name"
                                                   class="rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"/>
                                            <input :name="`days[${i}][locations][${j}][link_title]`" x-model="loc.link_title" placeholder="Link title (e.g., View Details)"
                                                   class="rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"/>
                                            <div class="flex gap-2">
                                                <input :name="`days[${i}][locations][${j}][link_url]`" x-model="loc.link_url" placeholder="Link URL"
                                                       class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"/>
                                                <button type="button" @click="removeLocation(i,j)"
                                                        class="rounded-lg border border-gray-200 px-3 text-sm hover:bg-gray-50">Remove</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Itinerary Details -->
                            <div class="mt-6 rounded-xl border border-gray-100 bg-red-50/40 p-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-semibold text-gray-900">Itinerary Details</h4>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <div class="space-y-2">
                                        <input :name="`days[${i}][itinerary_title]`" placeholder="Section title"
                                               class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"/>
                                        <div class="gap-2">
                                            <div
                                                x-bind:data-name="`days[${i}][itinerary_description]`"
                                                x-bind:data-id="`days[${i}][itinerary_description]`"
                                            >                                                
                                                <x-trix-input-alpine
                                                    placeholder="Section description"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Resource Buttons -->
                            <div class="mt-6 rounded-xl border border-gray-100 bg-red-50/40 p-4">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-semibold text-gray-900">Additional Resource Buttons</h4>
                                    <button type="button" @click="addResource(i)"
                                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                                        ＋ Add Button
                                    </button>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <template x-for="(res, r) in day.resources" :key="r">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <input :name="`days[${i}][resources][${r}][title]`" x-model="res.title" placeholder="Button title"
                                                   class="rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"/>
                                            <div class="flex gap-2">
                                                <input :name="`days[${i}][resources][${r}][url]`" x-model="res.url" placeholder="Button link URL"
                                                       class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500"/>
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
                <div class="w-1/3 justify-center" x-data="{sponsorImageFile: null, defaultPreview: ''}">
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
                                x-bind:src="sponsorImageFile ? URL.createObjectURL(sponsorImageFile) : ''"
                                x-show="sponsorImageFile"
                            />
                            <div x-show="!sponsorImageFile" class="w-full h-full flex flex-col items-center justify-center">
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

            <!-- Submit -->
            <div class="flex justify-end gap-3">
                <a href="{{ url()->previous() }}" 
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">
                    Cancel
                </a>
                <button 
                    type="submit" 
                    :disabled="loading"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <template x-if="loading">
                        <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                    <template x-if="!loading">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6v6l4 2M5 13a7 7 0 1114 0 7 7 0 01-14 0z" />
                        </svg>
                    </template>
                    <span x-text="loading ? 'Saving...' : 'Save Event'"></span>
                </button>
            </div>
        </form>
    </div>

    <script>
        function eventForm() {
            return {
                days: [],
                loading: false,

                // Dynamic handlers
                addDay() {
                    this.days.push({
                        title: '', date: '', subtitle: '',
                        locations: [], resources: []
                    });
                },
                removeDay(i) { this.days.splice(i, 1); },
                moveDayUp(i) { if (i > 0) { const d = this.days.splice(i, 1)[0]; this.days.splice(i - 1, 0, d); } },
                moveDayDown(i) { if (i < this.days.length - 1) { const d = this.days.splice(i, 1)[0]; this.days.splice(i + 1, 0, d); } },

                addLocation(i) { this.days[i].locations.push({ name: '', link_title: '', link_url: '' }); },
                removeLocation(i, j) { this.days[i].locations.splice(j, 1); },

                addResource(i) { this.days[i].resources.push({ title: '', url: '' }); },
                removeResource(i, r) { this.days[i].resources.splice(r, 1); },
            }
        }
    </script>
</x-app-layout>
