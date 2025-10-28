<x-app-layout>
    <div class="max-w-7xl mx-auto p-6" x-data="usersPage()">

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>

            <button @click="openCreate()"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                <i class="fa-solid fa-user-plus"></i> Add User
            </button>
        </div>

        <!-- Search -->
        <form method="GET" class="mb-4">
            <input name="q" value="{{ $q }}" placeholder="Search users by name, email, or role…"
                   class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
        </form>

        <!-- Tabs (optional simplified) -->
        <div class="mb-3 flex gap-2">
            <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-sm">All Users</span>
        </div>

        <!-- Table -->
        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-gray-600">
                            <th class="px-4 py-2 font-medium">User</th>
                            <th class="px-4 py-2 font-medium">Username</th>
                            <th class="px-4 py-2 font-medium">Roles</th>
                            <th class="px-4 py-2 font-medium">Contact</th>
                            <th class="px-4 py-2 font-medium">Assigned Events</th>
                            <th class="px-4 py-2 font-medium">Status</th>
                            <th class="px-4 py-2 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $u)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $u->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $u->email }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $u->username }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($u->roles as $r)
                                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs">{{ $r->name }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div>{{ $u->phone ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="max-w-[260px] truncate">
                                        {{ $u->assignedEvents->pluck('title')->join(', ') ?: '—' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs {{ $u->status === 'active' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $u->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEdit(@js($u), @js($u->roles->pluck('name')), @js($u->assignedEvents->pluck('id')))"
                                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs hover:bg-gray-50">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="POST" action="{{ route('users.destroy', $u) }}"
                                              onsubmit="return confirm('Delete this user?')">
                                            @csrf @method('DELETE')
                                            <button class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs text-red-600 hover:bg-gray-50">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t">
                {{ $users->links() }}
            </div>
        </div>

        <!-- Modal -->
        <div x-show="open" x-cloak class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center">
            <div @click.outside="close()" class="bg-white rounded-2xl w-full max-w-3xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4" x-text="mode === 'create' ? 'Add User' : 'Edit User'"></h2>

                <form :action="mode === 'create' ? '{{ route('users.store') }}' : updateAction" method="POST">
                    @csrf
                    <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Username</label>
                            <input name="username" x-model="form.username" required
                                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input name="email" type="email" x-model="form.email" required
                                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">First Name</label>
                            <input name="first_name" x-model="form.first_name"
                                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input name="last_name" x-model="form.last_name"
                                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input name="phone" x-model="form.phone"
                                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" x-model="form.status"
                                    class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input :required="mode==='create'" name="password" type="password"
                                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input :required="mode==='create'" name="password_confirmation" type="password"
                                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Vehicle Code</label>
                            <input name="vehicle_code" x-model="form.vehicle_code"
                                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-xl border border-gray-200 p-4">
                            <div class="font-semibold text-gray-900 mb-2">Roles</div>
                            <div class="space-y-2">
                                @foreach($roles as $role)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="roles[]" :checked="form.roles.includes('{{ $role }}')" value="{{ $role }}">
                                        <span>{{ $role }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 p-4">
                            <div class="font-semibold text-gray-900 mb-2">Assigned Events</div>
                            <div class="max-h-56 overflow-y-auto space-y-2">
                                @foreach($events as $e)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="assigned_events[]" :checked="form.assigned_events.includes({{ $e->id }})" value="{{ $e->id }}">
                                        <span>{{ $e->title }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="close()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            <span x-text="mode==='create' ? 'Create User' : 'Save Changes'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function usersPage() {
            return {
                open: false,
                mode: 'create',
                updateAction: '',
                form: {
                    username: '', email: '', first_name: '', last_name: '',
                    phone: '', status: 'active', vehicle_code: '',
                    roles: [], assigned_events: []
                },
                openCreate() {
                    this.mode = 'create';
                    this.updateAction = '';
                    this.form = { username:'', email:'', first_name:'', last_name:'', phone:'', status:'active', vehicle_code:'', roles:[], assigned_events:[] };
                    this.open = true;
                },
                openEdit(user, roles, eventIds) {
                    this.mode = 'edit';
                    this.updateAction = `{{ url('users') }}/${user.id}`;
                    this.form = {
                        username: user.username,
                        email: user.email,
                        first_name: user.first_name ?? '',
                        last_name: user.last_name ?? '',
                        phone: user.phone ?? '',
                        status: user.status,
                        vehicle_code: user.vehicle_code ?? '',
                        roles: roles ?? [],
                        assigned_events: eventIds ?? []
                    };
                    this.open = true;
                },
                close(){ this.open = false; }
            }
        }
    </script>
</x-app-layout>
