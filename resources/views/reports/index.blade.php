@extends('layouts.app')

@section('content')
<div class="py-10 bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Reports Dashboard</h2>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow">
                {{ session('error') }}
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h4 class="text-sm font-medium text-gray-500">Residents</h4>
                <p class="text-2xl font-bold text-gray-800">{{ $reports['residentStats']['total'] }}</p>
                <p class="text-green-600 text-sm">Active this month: {{ $reports['residentStats']['active'] }}</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h4 class="text-sm font-medium text-gray-500">Issues</h4>
                <p class="text-2xl font-bold text-gray-800">{{ $reports['issueStats']['total'] }}</p>
                <p class="text-green-600 text-sm">Resolved: {{ $reports['issueStats']['resolved'] }}</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h4 class="text-sm font-medium text-gray-500">Payments</h4>
                <p class="text-2xl font-bold text-gray-800">ZMW {{ number_format($reports['paymentStats']['collected'], 2) }}</p>
                <p class="text-red-600 text-sm">Outstanding: {{ $reports['paymentStats']['outstanding'] }}</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h4 class="text-sm font-medium text-gray-500">Collections</h4>
                <p class="text-2xl font-bold text-gray-800">{{ $reports['pickupStats']['total'] }}</p>
                <p class="text-green-600 text-sm">Completed: {{ $reports['pickupStats']['completed'] }}</p>
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Residents Activity</h3>
                <canvas id="residentsChart"></canvas>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Issues Status</h3>
                <canvas id="issuesChart"></canvas>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Payments Overview</h3>
                <canvas id="paymentsChart"></canvas>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Collections Performance</h3>
                <canvas id="collectionsChart"></canvas>
            </div>
        </div>

        <div class="mt-10">
            <a href="{{ url()->previous() }}" class="inline-block text-green-600 hover:underline">
                ← Back
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Residents Chart
    new Chart(document.getElementById('residentsChart'), {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive'],
            datasets: [{
                data: [
                    {{ $reports['residentStats']['active'] }},
                    {{ $reports['residentStats']['total'] - $reports['residentStats']['active'] }}
                ],
                backgroundColor: ['#16a34a', '#d1d5db'],
            }]
        }
    });

    // Issues Chart
    new Chart(document.getElementById('issuesChart'), {
        type: 'pie',
        data: {
            labels: ['Resolved', 'Unresolved'],
            datasets: [{
                data: [
                    {{ $reports['issueStats']['resolved'] }},
                    {{ $reports['issueStats']['total'] - $reports['issueStats']['resolved'] }}
                ],
                backgroundColor: ['#3b82f6', '#ef4444'],
            }]
        }
    });

    // Payments Chart
    new Chart(document.getElementById('paymentsChart'), {
        type: 'bar',
        data: {
            labels: ['Collected', 'Outstanding'],
            datasets: [{
                label: 'Payments',
                data: [
                    {{ $reports['paymentStats']['collected'] }},
                    {{ $reports['paymentStats']['outstanding'] }}
                ],
                backgroundColor: ['#10b981', '#f59e0b'],
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Collections Chart
    new Chart(document.getElementById('collectionsChart'), {
        type: 'bar',
        data: {
            labels: ['Completed', 'Pending'],
            datasets: [{
                label: 'Collections',
                data: [
                    {{ $reports['pickupStats']['completed'] }},
                    {{ $reports['pickupStats']['total'] - $reports['pickupStats']['completed'] }}
                ],
                backgroundColor: ['#2563eb', '#9ca3af'],
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endsection
