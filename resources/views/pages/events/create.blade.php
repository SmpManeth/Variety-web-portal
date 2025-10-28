<x-app-layout>
    <div class="max-w-6xl mx-auto p-6">
        <!-- ✅ Global Success Message -->
        @if(session('success'))
            <div x-data="{ show: true }"
                 x-show="show"
                 x-transition
                 class="mb-4 flex items-center justify-between rounded-md bg-green-50 p-3 text-green-800 border border-green-200">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-green-700 hover:text-green-900">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- ❌ Global Error Message -->
        @if($errors->has('general'))
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-3 text-red-700">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>
                {{ $errors->first('general') }}
            </div>
        @endif

        <!-- ❌ Validation Summary -->
        @if ($errors->any() && !$errors->has('general'))
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

        <form x-data="eventForm()" x-init="init()" method="POST"
              action="{{ route('events.store') }}"
              enctype="multipart/form-data"
              class="space-y-8">
            @csrf

            <!-- ==================== EVENT INFORMATION ==================== -->
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fa-regular fa-calendar text-gray-700 text-lg"></i>
                    <h2 class="text-xl font-semibold text-gray-900">Event Information</h2>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <!-- Event Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Event Title *</label>
                        <input name="title" type="text" value="{{ old('title') }}"
                               class="mt-1 w-full rounded-lg border {{ $errors->has('title') ? 'border-red-500' : 'border-gray-300' }} focus:ring-red-500 focus:border-red-500"
                               placeholder="Enter event title" required>
                        @error('title')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description *</label>
                        <textarea name="description" rows="5" required
                                  class="mt-1 w-full rounded-lg border {{ $errors->has('description') ? 'border-red-500' : 'border-gray-300' }} focus:ring-red-500 focus:border-red-500"
                                  placeholder="Describe the event">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dates + Participants -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Date *</label>
                            <input name="start_date" type="date" value="{{ old('start_date') }}"
                                   class="mt-1 w-full rounded-lg border {{ $errors->has('start_date') ? 'border-red-500' : 'border-gray-300' }} focus:ring-red-500 focus:border-red-500" required>
                            @error('start_date')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Date *</label>
                            <input name="end_date" type="date" value="{{ old('end_date') }}"
                                   class="mt-1 w-full rounded-lg border {{ $errors->has('end_date') ? 'border-red-500' : 'border-gray-300' }} focus:ring-red-500 focus:border-red-500" required>
                            @error('end_date')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Max Participants *</label>
                            <input name="max_participants" type="number" min="1" value="{{ old('max_participants', 50) }}"
                                   class="mt-1 w-full rounded-lg border {{ $errors->has('max_participants') ? 'border-red-500' : 'border-gray-300' }} focus:ring-red-500 focus:border-red-500" required>
                            @error('max_participants')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================== EVENT ITINERARY ==================== -->
            <section class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 pt-6">
                    <h2 class="text-xl font-semibold text-gray-900">Event Itinerary</h2>
                    <button type="button" @click="addDay()"
                            class="inline-flex items-center gap-2 rounded-lg bg-white border border-gray-200 px-4 py-2 text-sm font-medium hover:bg-gray-50">
                        <i class="fa-solid fa-plus"></i> Add Day
                    </button>
                </div>

                <template x-if="days.length === 0">
                    <div class="p-10 text-center text-gray-500">
                        <i class="fa-regular fa-calendar-days text-4xl text-gray-300"></i>
                        <p class="mt-2">No days added yet. Click “Add Day” to get started.</p>
                    </div>
                </template>

                <div class="p-6 space-y-8">
                    <template x-for="(day, i) in days" :key="i">
                        <!-- (Your existing Day sections remain unchanged) -->
                        <!-- Keep all add/remove/move logic as is -->
                        <div class="rounded-xl border border-gray-200 p-5 bg-white shadow-sm">
                            <div class="flex justify-between items-center">
                                <h3 class="font-semibold text-gray-800">Day <span x-text="i + 1"></span></h3>
                                <button type="button" @click="removeDay(i)" class="text-sm text-red-600 hover:underline">Remove</button>
                            </div>
                            <!-- The rest of your day/locations/details/resources UI stays same -->
                        </div>
                    </template>
                </div>
            </section>

            <!-- ==================== SPONSORS ==================== -->
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
                                <input :name="`sponsors[${s}][name]`" x-model="sp.name" placeholder="Sponsor name"
                                       class="rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                <div class="flex md:col-span-2 gap-2">
                                    <input :name="`sponsors[${s}][logo_url]`" x-model="sp.logo_url" placeholder="Logo URL"
                                           class="w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"/>
                                    <button type="button" @click="removeSponsor(s)"
                                            class="rounded-lg border border-gray-200 px-3 text-sm hover:bg-gray-50">Remove</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <!-- ==================== ACTION BUTTONS ==================== -->
            <div class="flex justify-end gap-3">
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Event
                </button>
            </div>
        </form>
    </div>

    <script>
        function eventForm() {
            return {
                days: [],
                sponsors: [],
                init() {
                    this.sponsors = [];
                },
                addDay() {
                    this.days.push({
                        title: '', date: '', subtitle: '',
                        locations: [], details: [], resources: []
                    });
                },
                removeDay(i){ this.days.splice(i,1); },
                moveDayUp(i){ if(i>0){ const d=this.days.splice(i,1)[0]; this.days.splice(i-1,0,d);} },
                moveDayDown(i){ if(i<this.days.length-1){ const d=this.days.splice(i,1)[0]; this.days.splice(i+1,0,d);} },
                addLocation(i){ this.days[i].locations.push({name:'',link_title:'',link_url:''}); },
                removeLocation(i,j){ this.days[i].locations.splice(j,1); },
                addDetail(i){ this.days[i].details.push({title:'',description:''}); },
                removeDetail(i,k){ this.days[i].details.splice(k,1); },
                addResource(i){ this.days[i].resources.push({title:'',url:''}); },
                removeResource(i,r){ this.days[i].resources.splice(r,1); },
                addSponsor(){ this.sponsors.push({name:'',logo_url:''}); },
                removeSponsor(s){ this.sponsors.splice(s,1); },
            }
        }
    </script>
</x-app-layout>
