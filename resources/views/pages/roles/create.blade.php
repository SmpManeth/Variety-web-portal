<x-app-layout>
    <div class="max-w-3xl mx-auto p-6">
        <h1 class="text-xl font-bold text-gray-900 mb-6">Create Role</h1>

        <form method="POST" action="{{ route('roles.store') }}" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Role Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" required>
                @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($permissions as $perm)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="rounded text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $perm->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('roles.index') }}" class="px-4 py-2 border rounded-lg text-gray-700">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Save</button>
            </div>
        </form>
    </div>
</x-app-layout>
