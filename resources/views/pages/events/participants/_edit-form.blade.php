<div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Participant</h3>
                            <form method="POST" :action="`/events/{{ $event->id }}/participants/${modalData.id}`">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Full Name</label>
                                        <input type="text" name="full_name" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-red-500" x-model="modalData.full_name" required>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Email</label>
                                        <input type="email" name="email" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-red-500" x-model="modalData.email">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Phone</label>
                                        <input type="text" name="phone" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-red-500" x-model="modalData.phone">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Vehicle</label>
                                        <input type="text" name="vehicle" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-red-500" x-model="modalData.vehicle">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Emergency Contact Name</label>
                                        <input type="text" name="emergency_contact_name" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-red-500" x-model="modalData.emergency_contact_name">
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Relationship</label>
                                        <input type="text" name="emergency_contact_relationship" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-red-500" x-model="modalData.emergency_contact_relationship">
                                    </div>
                                </div>

                                <div class="mt-4 flex justify-end gap-2">
                                    <button type="button" @click="openModal = false" class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700">Cancel</button>
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-sm text-white font-semibold hover:bg-red-700">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>