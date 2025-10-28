<x-app-layout>
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-xl font-bold text-gray-900">Roles</h1>
            <a href="{{ route('roles.create') }}" class="rounded-lg bg-red-600 text-white px-4 py-2 font-semibold text-sm hover:bg-red-700">
                <i class="fa-solid fa-plus"></i> New Role
            </a>
        </div>

        @if(session('success'))
            <div class="p-3 mb-4 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
        @endif

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full text-sm text-gray-700">
                <thead class="bg-gray-50">
                    <tr class="text-left">
                        <th class="p-3 font-semibold">Name</th>
                        <th class="p-3 font-semibold">Permissions</th>
                        <th class="p-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr class="border-t">
                            <td class="p-3 font-medium">{{ $role->name }}</td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($role->permissions as $perm)
                                        <span class="px-2 py-0.5 text-xs bg-gray-100 rounded">{{ $perm->name }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-3 text-right">
                                <a href="{{ route('roles.edit', $role) }}" class="text-blue-600 hover:text-blue-800">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this role?')" class="text-red-600 hover:text-red-800 ml-2">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($roles->isEmpty())
                        <tr><td colspan="3" class="p-4 text-center text-gray-500">No roles found.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
