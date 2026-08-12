<x-app-layout>
    <div class="max-w-7xl mx-auto p-6" x-data="{ open: false }">
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-5">
            <h1 class="text-2xl font-bold text-gray-900">Job Images for {{ $event->title }}</h1>
            <div class="flex gap-2">
                <form action="" class="flex gap-2">
                    <input type="text" placeholder="Search by Filename" name="search" value="{{ request('search') }}" class="rounded-lg">
                    <button
                        class="cursor-pointer inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </form>
                <button @click="open = true"
                    class="cursor-pointer inline-flex items-center gap-2 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
                    <i class="fa-solid fa-plus"></i> Add Images
                </button>
                <a href="{{ route('events.show', $event) }}"
                    class="cursor-pointer inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-chevron-left"></i> Back to Event
                </a>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium">Image</th>
                        <th class="px-4 py-2 text-left font-medium">Filename</th>
                        <th class="px-4 py-2 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($jobImages as $image)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-900">
                                <img src="{{ Storage::url($image->path) }}" class="w-48 rounded-lg" alt="Image Preview">
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $image->name }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ Storage::url($image->path) }}"
                                        class="border border-gray-300 px-3 py-1.5 rounded-lg hover:bg-gray-50 text-xs">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <form method="post" action="/job-images/{{ $event->id }}/{{ $image->id }}">
                                        @method('delete')
                                        @csrf
                                        <button
                                            type="submit"
                                            class="border border-gray-300 px-3 py-1.5 rounded-lg hover:bg-gray-50 text-xs text-red-800">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No images found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $jobImages->links() }}
        </div>

        <!-- Modal -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div @click.outside="open=false" class="bg-white rounded-2xl w-full max-w-md p-6">
                <form action="/job-images/{{$event->id}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label class="block text-sm font-medium text-gray-700" for="image">
                        <p>Images</p>
                        <div class="border border-gray-300 p-3 rounded-lg mt-2">
                            <input name="images[]" required placeholder="Select image" id="image" type="file" multiple
                                class="w-full" />
                        </div>
                    </label>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="open=false" class="border border-gray-300 bg-white rounded-lg px-4 py-2 text-sm hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="bg-red-600 rounded-lg px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            <span>Add Images</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
