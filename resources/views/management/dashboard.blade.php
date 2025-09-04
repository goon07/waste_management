@extends('layouts.app')

@section('content')
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Company Admin Dashboard</h2>

        <!-- Pending Pickups -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Pending Pickups</h3>
                <input type="text" placeholder="Search resident..." class="px-3 py-2 border rounded w-1/3" oninput="filterPickups(this.value)">
            </div>

            @if($pendingPickups->isEmpty())
                <p>No pending pickups.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded shadow">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left">Resident</th>
                                <th class="px-4 py-2 text-left">Waste Type</th>
                                <th class="px-4 py-2 text-left">Requested</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Assign To</th>
                                <th class="px-4 py-2 text-left">Schedule</th>
                                <th class="px-4 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody id="pickupTable">
                            @foreach($pendingPickups as $pickup)
                                <tr class="border-b">
                                    <td class="px-4 py-2">{{ $pickup->user->name ?? 'Unknown' }}</td>
                                    <td class="px-4 py-2">{{ ucfirst($pickup->waste_type) }}</td>
                                    <td class="px-4 py-2">{{ $pickup->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded text-sm 
                                            {{ $pickup->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($pickup->status == 'assigned' ? 'bg-blue-100 text-blue-800' : 
                                               'bg-green-100 text-green-800') }}">
                                            {{ ucfirst($pickup->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <form action="{{ route('management.assign_pickup', $pickup->id) }}" method="POST" class="flex space-x-2">
                                            @csrf
                                            <select name="collector_id" class="px-3 py-2 border rounded" required>
                                                <option value="">Select Collector</option>
                                                @foreach($collectors as $collector)
                                                    <option value="{{ $collector->id }}">{{ $collector->name }}</option>
                                                @endforeach
                                            </select>
                                    </td>
                                    <td class="px-4 py-2">
                                            <input type="date" name="scheduled_date" 
                                                   min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}" 
                                                   class="px-3 py-2 border rounded" required>
                                    </td>
                                    <td class="px-4 py-2">
                                            <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">Assign</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Quick Reports -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Quick Reports</h3>
                <select id="statusFilter" onchange="filterReports()" class="px-3 py-2 border rounded">
                    <option value="">Filter by Status</option>
                    <option value="pending">Pending</option>
                    <option value="assigned">Assigned</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            @if($reports->isEmpty())
                <p>No reports available.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded shadow">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left">Pickup ID</th>
                                <th class="px-4 py-2 text-left">Resident</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody id="reportTable">
                            @foreach($reports as $report)
                                <tr class="border-b" data-status="{{ strtolower($report['status']) }}">
                                    <td class="px-4 py-2">#{{ $report['pickup_id'] }}</td>
                                    <td class="px-4 py-2">{{ $report['resident_name'] ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded text-sm 
                                            {{ $report['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($report['status'] == 'assigned' ? 'bg-blue-100 text-blue-800' : 
                                               'bg-green-100 text-green-800') }}">
                                            {{ ucfirst($report['status']) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">{{ $report['created_at'] ?? '-' }}</td>
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
function filterPickups(query) {
    query = query.toLowerCase();
    document.querySelectorAll("#pickupTable tr").forEach(row => {
        const name = row.querySelector("td:first-child").textContent.toLowerCase();
        row.style.display = name.includes(query) ? "" : "none";
    });
}

function filterReports() {
    const status = document.getElementById("statusFilter").value;
    document.querySelectorAll("#reportTable tr").forEach(row => {
        if (!status || row.dataset.status === status) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>
@endsection
