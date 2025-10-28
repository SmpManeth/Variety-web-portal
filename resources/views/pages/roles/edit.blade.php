<x-app-layout>
    <div class="max-w-3xl mx-auto p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-900">Edit Role</h1>
            <a href="{{ route('roles.index') }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900">
                <i class="fa-solid fa-arrow-left"></i> Back to Roles
            </a>
        </div>

        <!-- Flash message -->
        @if(session('success'))
            <div class="p-3 mb-4 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-3 mb-4 bg-red-100 text-red-700 rounded">{{ session('error') }}</div>
        @endif

        <!-- Edit form -->
        <form method="POST"
              action="{{ route('roles.update', $role) }}"
              class="space-y-6"
              x-data="{ 
                  selectAll: false, 
                  toggleAll() {
                      this.selectAll = !this.selectAll;
                      document.querySelectorAll('input[name=\'permissions[]\']').forEach(el => el.checked = this.selectAll);
                  }
              }">
            @csrf
            @method('PUT')

            <!-- Role name -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Role Name</label>
                <input type="text"
                       name="name"
                       value="{{ old('name', $role->name) }}"
                       class="w-full border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500"
                       required>
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Permissions -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Permissions</label>
                    <button type="button"
                            @click="toggleAll()"
                            class="text-xs font-medium text-red-600 hover:underline">
                        <span x-text="selectAll ? 'Deselect All' : 'Select All'"></span>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-2 border rounded-lg p-3 bg-white">
                    @foreach($permissions as $perm)
                        <label class="inline-flex items-center">
                            <input type="checkbox"
                                   name="permissions[]"
                                   value="{{ $perm->name }}"
                                   @checked($role->permissions->contains('name', $perm->name))
                                   class="rounded text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $perm->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-2">
                <a href="{{ route('roles.index') }}" 
                   class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Update
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
