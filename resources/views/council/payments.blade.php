@extends('layouts.app')

@section('content')
<section class="py-20 bg-gray-100">
    <div id="bills" class="tab-pane" data-intro="Manage resident bills here">
        <div class="mt-6 bg-white rounded-xl shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6">Resident Bills</h3>

            {{-- Filters --}}
            <div class="flex flex-col md:flex-row md:items-center md:space-x-4 mb-6 space-y-3 md:space-y-0">
                <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                </select>
                <input type="month" id="monthFilter" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700" />
            </div>

            @if ($bills->isEmpty())
                <p class="text-gray-600">No bills found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg shadow-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Resident</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Month</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Amount</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Paid</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="billsTable" class="divide-y divide-gray-200">
                            @foreach ($bills as $bill)
                                <tr class="bill-row" 
                                    data-status="{{ strtolower($bill->status) }}" 
                                    data-month="{{ $bill->month }}">
                                    <td class="px-4 py-3 text-sm text-gray-800">{{ $bill->user->name ?? 'Unknown' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800">{{ \Carbon\Carbon::createFromDate($bill->year, $bill->month, 1)->format('F Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800">{{ number_format($bill->amount, 2) }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            @if($bill->status == 'paid') bg-green-100 text-green-700
                                            @elseif($bill->status == 'pending') bg-yellow-100 text-yellow-700
                                            @else bg-red-100 text-red-700 @endif">
                                            {{ ucfirst($bill->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800">
                                        {{ $bill->payments->sum('amount') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="{{ route('council.bill.details', $bill->id) }}" class="text-blue-600 hover:text-blue-800">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const statusFilter = document.getElementById('statusFilter');
    const monthFilter = document.getElementById('monthFilter');
    const rows = document.querySelectorAll('.bill-row');

    function filterBills() {
        const status = statusFilter.value;
        const month = monthFilter.value;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const rowMonth = row.getAttribute('data-month');
            const statusMatch = !status || rowStatus === status;
            const monthMatch = !month || rowMonth === parseInt(month.split('-')[1]);

            row.style.display = (statusMatch && monthMatch) ? '' : 'none';
        });
    }

    statusFilter.addEventListener('change', filterBills);
    monthFilter.addEventListener('change', filterBills);
});
</script>
@endsection
