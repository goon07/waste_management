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
            <div class="bg-white rounded-xl shadow-md p-6 overflow-x-auto">
                <table class="min-w-full table-auto border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200 text-gray-700">
                            <th class="border border-gray-300 px-4 py-2 text-left">Name</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Address</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Phone</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Email</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Bills & Payments</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($residents as $resident)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2">{{ $resident->name ?? 'Unknown' }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $resident->address ?? 'N/A' }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $resident->phone_number ?? 'N/A' }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $resident->email ?? 'N/A' }}</td>
                                <td class="border border-gray-300 px-4 py-2 max-w-xs">
                                    @if($resident->bills->isEmpty())
                                        <span class="text-gray-500">No bills</span>
                                    @else
                                        @foreach($resident->bills as $bill)
                                            <div class="mb-3">
                                                <div class="font-semibold text-gray-700">
                                                    Bill: {{ $bill->month }}/{{ $bill->year }} - Amount: {{ number_format($bill->amount, 2) }} USD - Status: {{ ucfirst($bill->status) }}
                                                </div>
                                                @if($bill->payments->isEmpty())
                                                    <div class="text-sm text-gray-500">No payments</div>
                                                @else
                                                    <ul class="text-sm list-disc list-inside max-h-24 overflow-y-auto">
                                                        @foreach($bill->payments as $payment)
                                                            <li>
                                                                {{ number_format($payment->amount, 2) }} USD on {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }} ({{ ucfirst($payment->status) }})
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-4 py-2 text-green-600">
                                    <button onclick="openMessageModal('{{ $resident->id }}', '{{ $resident->name ?? 'Resident' }}')" class="hover:underline">Message</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

<!-- Message Modal -->
<div id="messageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-lg">
        <h3 class="text-xl font-bold text-gray-800 mb-4" id="messageModalTitle"></h3>
        <form id="messageForm" method="POST">
            @csrf
            <textarea name="message" class="w-full px-4 py-3 border border-gray-300 rounded-lg" rows="4" required></textarea>
            @error('message')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
            <div class="flex justify-end mt-4 space-x-2">
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