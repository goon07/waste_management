@extends('layouts.app')

@section('content')
<section class="py-20 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Residents</h2>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if($residents->isEmpty())
            <p>No residents assigned to your company yet.</p>
        @else
            <div class="bg-white rounded-xl shadow-md p-6">
                <ul class="divide-y divide-gray-200">
                    @foreach($residents as $resident)
                        <li class="py-4 flex justify-between items-center">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $resident->name ?? 'Unknown' }}</p>
                                <p class="text-sm text-gray-600">Address: {{ $resident->address ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600">Phone: {{ $resident->phone_number ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600">Email: {{ $resident->email ?? 'N/A' }}</p>
                            </div>
                            <button onclick="openMessageModal('{{ $resident->id }}', '{{ $resident->name ?? 'Resident' }}')" class="text-green-600 hover:underline">Message</button>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</section>

<!-- Include the same message modal from dashboard if needed -->
<div id="messageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-xl font-bold text-gray-800 mb-4" id="messageModalTitle"></h3>
        <form id="messageForm" method="POST">
            @csrf
            <textarea name="message" class="w-full px-4 py-3 border border-gray-300 rounded-lg" rows="4" required></textarea>
            @error('message')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
            <div class="flex justify-end mt-4">
                <button type="button" onclick="closeMessageModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">Send</button>
            </div>
        </form>
    </div>
</div>

<script>
function openMessageModal(residentId, residentName) {
    document.getElementById('messageModal').classList.remove('hidden');
    document.getElementById('messageModalTitle').textContent = `Message ${residentName}`;
    document.getElementById('messageForm').action = `/management/message/${residentId}`;
}

function closeMessageModal() {
    document.getElementById('messageModal').classList.add('hidden');
    document.getElementById('messageForm').reset();
}
</script>
@endsection
