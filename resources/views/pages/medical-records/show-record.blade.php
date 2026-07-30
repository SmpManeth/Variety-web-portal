<x-app-layout>
    <div class="max-w-7xl mx-auto p-4">

        @php
            $rawContent = $record->content;
            $content = is_array($rawContent)
                ? (object) $rawContent
                : json_decode($rawContent ?? '{}');
            if (! is_object($content)) {
                $content = (object) [];
            }
            $participant = $record->participant;
            $firstName = $participant?->first_name ?? ($content->first_name ?? '');
            $lastName = $participant?->last_name ?? ($content->last_name ?? '');
            $displayName = trim(($firstName . ' ' . $lastName)) !== '' ? trim($firstName . ' ' . $lastName) : 'Participant';
        @endphp

        @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="items-center justify-between mb-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('medical-records.show', $event) }}"
                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                    <i class="fa-solid fa-chevron-left"></i>
                    Back to {{ $event->title }} Medical Records
                </a>

                <div class="flex gap-2">
                    <a href="{{ route('medical-records.edit-record', [$event, $record]) }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-pen-to-square"></i> Edit record
                    </a>
                    <a href="{{ route('events.show', $event) }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-eye"></i> View Event
                    </a>
                </div>
            </div>
            <div class="mt-2">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Medical Record</h1>
                <p class="text-gray-500 text-sm">Viewing {{ $displayName }}'s Medical Record</p>
            </div>

        </div>

        <div class="mb-4 text-sm text-black flex gap-4">
            <div class="mb-1">
                <span class="font-semibold">Import Date:</span>
                <span>{{ $record->imported_at->format('d/m/Y') }}</span>
            </div>
            <div>
                <span class="font-semibold">Destroy Date:</span>
                <span>{{ $record->expires_at->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="flex gap-4">
            <div class="w-1/2">
                <div class="mb-4 rounded-xl bg-white p-4 shadow">
                    <h3 class="text-lg font-bold">Personal Details</h3>
                    <hr class="my-4">

                    {{-- Name --}}
                    <h4 class="font-bold">Name</h4>
                    <p class="mb-4">{{$content->first_name ?? ''}} {{$content->last_name ?? ''}} @if(!empty($content->nickname)) ({{ $content->nickname }}) @endif</p>

                    {{-- Vehicle --}}
                    <h4 class="font-bold">Vehicle</h4>
                    <p class="mb-4">{{$content->vehicle ?? '—'}}</p>

                    {{-- Address --}}
                    <h4 class="font-bold">Address</h4>
                    <p class="mb-4">
                        @if(!empty($content->address1)){{$content->address1}}<br/>@endif
                        @if(!empty($content->address2)){{$content->address2}}<br/>@endif
                        @if(!empty($content->address3)){{$content->address3}}<br/>@endif
                        @if(!empty($content->address4)){{$content->address4}}<br/>@endif
                        @if(!empty($content->address5)){{$content->address5}}<br/>@endif
                        @if(!empty($content->address6)){{$content->address6}}@endif
                    </p>

                    {{-- Phone --}}
                    <h4 class="font-bold">Phone</h4>
                    <p class="mb-4">{{ $content->mobile ?? '—' }}</p>
                </div>

                <div class="mb-4 rounded-xl bg-white p-4 shadow">
                    <h3 class="text-lg font-bold">Next of kin</h3>
                    <hr class="my-4">

                    {{-- Name --}}
                    <h4 class="font-bold">Name</h4>
                    <p class="mb-4">{{$content->next_of_kin ?? '—'}}</p>

                    {{-- Contact Phone --}}
                    <h4 class="font-bold">Contact Phone</h4>
                    <p class="mb-4">{{$content->nok_phone ?? '—'}}</p>

                    {{-- Alternate Contact Phone --}}
                    <h4 class="font-bold">Alternate Contact Phone</h4>
                    <p class="mb-4">{{$content->nok_alt_phone ?? '—'}}</p>
                </div>

                <div class="mb-4 rounded-xl bg-white p-4 shadow">
                    <h3 class="text-lg font-bold">Medical Details</h3>
                    <hr class="my-4">

                    {{-- Date of Birth --}}
                    <h4 class="font-bold">Date of Birth</h4>
                    <p class="mb-4">{{ isset($content->dob) ? \Carbon\Carbon::parse($content->dob)->format('d/m/Y') : '—' }}</p>

                    {{-- Allergies --}}
                    <h4 class="font-bold">Allergies</h4>
                    <p class="mb-4">{{$content->allergies ? $content->allergies : 'None'}}</p>

                    {{-- Dietary Requirements --}}
                    <h4 class="font-bold">Dietary Requirements</h4>
                    <p class="mb-4">{{$content->dietary_requirement ? $content->dietary_requirement : 'None'}}</p>

                    {{-- Current Medications --}}
                    <h4 class="font-bold">Current Medications</h4>
                    <p class="mb-4">{{$content->current_medications ? $content->current_medications : 'None'}}</p>

                    {{-- Current Medical Conditions --}}
                    <h4 class="font-bold">Current Medical Conditions</h4>
                    <p class="mb-4">{{$content->current_medical_history ? $content->current_medical_history : 'None'}}</p>

                </div>

            </div>
            <div class="w-1/2">
                <div class="mb-4 rounded-xl bg-white p-4 shadow">
                    <h3 class="text-lg font-bold">Comments</h3>
                    <hr class="my-4">

                    @forelse($record->comments as $comment)
                    <div class="mb-4">
                        <p>{{ $comment->content }}</p>
                        <div class="flex justify-between">
                            <p class="text-neutral-800 text-sm">
                                {{ $comment->created_at->format('M j, Y h:i:s') }}
                            </p>
                            <form action="{{ route('medical-record-comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this comment?');">
                                @method("DELETE")
                                @csrf
                                <button type="submit" class="text-red-600 text-sm">Remove</button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="mb-4">
                        <p class="text-neutral-600 text-sm">No comments found</p>
                    </div>
                    @endforelse

                    <form action="{{ route('medical-record-comments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="medical_record_id" value="{{ $record->id }}">
                        <textarea class="w-full rounded-lg mb-2" name="comment"></textarea>
                        <div class="flex justify-end">
                            <button class="bg-red-600 p-3 text-white rounded-lg" type="submit">Add Comment</button>
                        </div>
                    </form>
                </div>

                <div class="mb-4 rounded-xl bg-white p-4 shadow">
                    <h3 class="text-lg font-bold">Images</h3>
                    <hr class="my-4">

                    @forelse($record->images as $image)
                        @php
                            $encryptedContent = Illuminate\Support\Facades\Storage::disk('private')->get($image->path);
                            $decryptedContent = Illuminate\Support\Facades\Crypt::decrypt($encryptedContent);
                            $base64Image = base64_encode($decryptedContent);
                            $src = 'data:' . $image->mime . ';base64,' . $base64Image;
                        @endphp
                        <div class="mb-4">
                            <img src="{{ $src }}" alt="" class="rounded-lg w-full mb-2">
                            <div class="flex justify-between">
                                <p class="text-neutral-800 text-sm">
                                    {{ $image->created_at->format('M j, Y h:i:s') }}
                                </p>
                                <form action="{{ route('medical-record-images.destroy', $image) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this image?');">
                                    @method("DELETE")
                                    @csrf
                                    <button type="submit" class="text-red-600 text-sm">Remove</button>
                                </form>
                            </div>
                        </div>
                    @empty
                    <div class="mb-4">
                        <p class="text-neutral-600 text-sm">No comments found</p>
                    </div>
                    @endforelse

                    <form action="{{ route('medical-record-images.store') }}" method="POST" enctype="multipart/form-data" class="flex justify-between items-center">
                        @csrf
                        <input type="hidden" name="medical_record_id" value="{{ $record->id }}">
                        <input type="file" name="image" />
                        <button class="bg-red-600 p-3 text-white rounded-lg">Add Image</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
