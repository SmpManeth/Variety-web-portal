<x-app-layout>
    <div class="max-w-4xl mx-auto p-6" x-data="{ open:false, edit:false, perm:{} }">
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-5">
            <h1 class="text-2xl font-bold text-gray-900">Permissions</h1>
            <button @click="open=true; edit=false; perm={name:''}"
                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                <i class="fa-solid fa-plus"></i> Add Permission
            </button>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium">Permission</th>
                        <th class="px-4 py-2 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($permissions as $p)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-gray-900">{{ $p->name }}</td>
                            <td class="px-4 py-3 text-right flex justify-end gap-2">
                                <button @click="open=true; edit=true; perm={id:'{{ $p->id }}', name:'{{ $p->name }}'}"
                                    class="border border-gray-300 px-3 py-1.5 rounded-lg hover:bg-gray-50 text-xs">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form method="POST" action="{{ route('permissions.destroy', $p) }}" onsubmit="return confirm('Delete this permission?')">
                                    @csrf @method('DELETE')
                                    <button class="border border-gray-300 px-3 py-1.5 rounded-lg text-xs text-red-600 hover:bg-gray-50">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-4 py-6 text-center text-gray-500">No permissions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $permissions->links() }}
        </div>

        <!-- Modal -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div @click.outside="open=false" class="bg-white rounded-2xl w-full max-w-md p-6">
                <form :action="edit ? '{{ url('permissions') }}/'+perm.id : '{{ route('permissions.store') }}'" method="POST">
                    @csrf
                    <template x-if="edit"><input type="hidden" name="_method" value="PUT"></template>

                    <h2 class="text-lg font-semibold text-gray-900 mb-4" x-text="edit ? 'Edit Permission' : 'Add Permission'"></h2>

                    <label class="block text-sm font-medium text-gray-700">Permission Name</label>
                    <input name="name" x-model="perm.name" required
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="open=false" class="border border-gray-300 bg-white rounded-lg px-4 py-2 text-sm hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="bg-red-600 rounded-lg px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            <span x-text="edit ? 'Save Changes' : 'Create Permission'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
