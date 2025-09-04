@extends('layouts.app')

@section('content')
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Reports & Analytics</h2>

        {{-- Flash Messages --}}
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

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white shadow rounded-lg p-5">
                <h3 class="text-gray-500 text-sm font-semibold">Total Residents</h3>
                <p class="mt-2 text-2xl font-bold text-gray-800">{{ $reports['residentStats']['total'] }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-5">
                <h3 class="text-gray-500 text-sm font-semibold">Active Residents (This Month)</h3>
                <p class="mt-2 text-2xl font-bold text-gray-800">{{ $reports['residentStats']['active'] }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-5">
                <h3 class="text-gray-500 text-sm font-semibold">Total Issues</h3>
                <p class="mt-2 text-2xl font-bold text-gray-800">{{ $reports['issueStats']['total'] }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-5">
                <h3 class="text-gray-500 text-sm font-semibold">Resolved Issues</h3>
                <p class="mt-2 text-2xl font-bold text-gray-800">{{ $reports['issueStats']['resolved'] }}</p>
            </div>
        </div>

        {{-- Payments & Pickups Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
            <div class="bg-white shadow rounded-lg p-5">
                <h3 class="text-gray-700 text-lg font-semibold mb-4">Payments Overview</h3>
                <canvas id="paymentsChart" class="w-full h-64"></canvas>
                <div class="mt-4 flex justify-between text-gray-600 text-sm">
                    <span>Collected: {{ number_format($reports['paymentStats']['collected'], 2) }}</span>
                    <span>Outstanding: {{ $reports['paymentStats']['outstanding'] }}</span>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-5">
                <h3 class="text-gray-700 text-lg font-semibold mb-4">Pickup Status</h3>
                <canvas id="pickupChart" class="w-full h-64"></canvas>
                <div class="mt-4 flex justify-between text-gray-600 text-sm">
                    <span>Total: {{ $reports['pickupStats']['total'] }}</span>
                    <span>Completed: {{ $reports['pickupStats']['completed'] }}</span>
                </div>
            </div>
        </div>

        {{-- Optional: Detailed Tables --}}
        <div class="bg-white shadow rounded-lg p-5">
            <h3 class="text-gray-700 text-lg font-semibold mb-4">Recent Collections</h3>
            @if ($collections->isEmpty())
                <p class="text-gray-600">No collections available.</p>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pickup ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Resident</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Waste Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Feedback</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($collections as $c)
                            <tr>
                                <td class="px-4 py-2">{{ $c->id }}</td>
                                <td class="px-4 py-2">{{ $c->user->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $c->wasteType->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ ucfirst($c->status) }}</td>
                                <td class="px-4 py-2">{{ $c->rating ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $c->feedback ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const paymentsCtx = document.getElementById('paymentsChart').getContext('2d');
    new Chart(paymentsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Collected', 'Outstanding'],
            datasets: [{
                data: [{{ $reports['paymentStats']['collected'] }}, {{ $reports['paymentStats']['outstanding'] }}],
                backgroundColor: ['#16a34a', '#f87171'],
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    const pickupCtx = document.getElementById('pickupChart').getContext('2d');
    new Chart(pickupCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Pending'],
            datasets: [{
                data: [{{ $reports['pickupStats']['completed'] }}, {{ $reports['pickupStats']['total'] - $reports['pickupStats']['completed'] }}],
                backgroundColor: ['#2563eb', '#fbbf24'],
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
@endsection
