<div>
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Participant</h3>
    <form method="POST" :action="`/events/{{ $event->id }}/participants/${modalData.id}`" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">First Name *</label>
                <input type="text" name="first_name" required
                       x-model="modalData.first_name"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Last Name</label>
                <input type="text" name="last_name"
                       x-model="modalData.last_name"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email"
                       x-model="modalData.email"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone"
                       x-model="modalData.phone"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Vehicle</label>
                <input type="text" name="vehicle"
                       x-model="modalData.vehicle"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Status *</label>
                <select name="status" required
                        x-model="modalData.status"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Roles</label>
                <select name="roles[]" multiple
                        x-model="modalData.roles"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                    @foreach(\App\Models\Role::whereNotIn('name', ['Super Admin', 'Administrator'])->get() as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple roles</p>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900 mb-2">Emergency Contact Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Contact Name</label>
                    <input type="text" name="emergency_contact_name"
                           x-model="modalData.emergency_contact_name"
                           class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Contact Phone</label>
                    <input type="text" name="emergency_contact_phone"
                           x-model="modalData.emergency_contact_phone"
                           class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Relationship</label>
                    <input type="text" name="emergency_contact_relationship"
                           x-model="modalData.emergency_contact_relationship"
                           class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
                </div>
            </div>
        </div>

        <div class="mt-4 flex justify-end gap-3">
            <button type="button" @click="openModal = false"
                    class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-red-600 text-sm text-white font-semibold hover:bg-red-700">
                Save Changes
            </button>
        </div>
    </form>
</div>
