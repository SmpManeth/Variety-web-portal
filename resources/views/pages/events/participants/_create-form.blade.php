<form method="POST" action="{{ route('participants.store', $event) }}" class="space-y-6">
    @csrf

    <h3 class="text-lg font-semibold text-gray-900">Add New Participant</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">First Name *</label>
            <input type="text" name="first_name" required
                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Last Name</label>
            <input type="text" name="last_name"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Phone</label>
            <input type="text" name="phone"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Age</label>
            <input type="number" name="age" min="1"
                   class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <div class="pt-4 border-t border-gray-200">
        <h4 class="text-sm font-semibold text-gray-900 mb-2">Emergency Contact Information</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Contact Name</label>
                <input type="text" name="emergency_contact_name"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Contact Phone</label>
                <input type="text" name="emergency_contact_phone"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Relationship</label>
                <input type="text" name="emergency_contact_relationship"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500" />
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <button type="button"
                @click="$dispatch('close-modal')"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">
            Cancel
        </button>

        <button type="submit"
                class="rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700">
            Add Participant
        </button>
    </div>
</form>
