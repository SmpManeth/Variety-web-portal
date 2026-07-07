<form @submit.prevent="submitForm" class="space-y-6">
    <h3 class="text-lg font-semibold text-gray-900" x-text="modalType === 'edit' ? 'Edit Participant' : 'Add New Participant'"></h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">First Name *</label>
            <input type="text" x-model="modalData.first_name" required
                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" :class="errors.first_name ? 'border-red-500 ring-red-200' : ''" />
            <p x-show="errors.first_name" x-text="errors.first_name?.[0]" class="text-xs text-red-600 mt-1" x-cloak></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Last Name</label>
            <input type="text" x-model="modalData.last_name"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" x-model="modalData.email"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" :class="errors.email ? 'border-red-500' : ''" />
            <p x-show="errors.email" x-text="errors.email?.[0]" class="text-xs text-red-600 mt-1" x-cloak></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Phone</label>
            <input type="text" x-model="modalData.phone"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Vehicle</label>
            <input type="text" x-model="modalData.vehicle"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Status *</label>
            <select x-model="modalData.status" required
                    class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Roles</label>
            <select multiple x-model="modalData.roles"
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
                <input type="text" x-model="modalData.emergency_contact_name" class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Contact Phone</label>
                <input type="text" x-model="modalData.emergency_contact_phone" class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Relationship</label>
                <input type="text" x-model="modalData.emergency_contact_relationship" class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <button type="button" @click.stop="openModal = false" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-red-600 text-sm font-semibold text-white hover:bg-red-700 flex items-center gap-2" :disabled="submitting">
            <span x-show="!submitting" x-text="modalType === 'edit' ? 'Save Changes' : 'Add Participant'"></span>
            <span x-show="submitting" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Processing...</span>
            </span>
        </button>
    </div>
</form>
