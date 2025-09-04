@extends('layouts.app')

@section('content')
<section class="py-10 bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4">

        <h2 class="text-3xl font-bold text-gray-800 mb-6">
            {{ ucfirst($user->role) }} Dashboard Reports
        </h2>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white shadow rounded-lg p-6 border-l-4 border-blue-500">
                <h3 class="text-lg font-semibold text-gray-600">Total Users</h3>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['users'] }}</p>
            </div>

            <div class="bg-white shadow rounded-lg p-6 border-l-4 border-red-500">
                <h3 class="text-lg font-semibold text-gray-600">Total Issues</h3>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['issues'] }}</p>
            </div>

            <div class="bg-white shadow rounded-lg p-6 border-l-4 border-green-500">
                <h3 class="text-lg font-semibold text-gray-600">Payments Collected</h3>
                <p class="text-3xl font-bold text-gray-800">ZMW {{ number_format($stats['payments'], 2) }}</p>
            </div>

            <div class="bg-white shadow rounded-lg p-6 border-l-4 border-yellow-500">
                <h3 class="text-lg font-semibold text-gray-600">Total Pickups</h3>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['pickups'] }}</p>
            </div>
        </div>

        <!-- Placeholder for Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">User Registration Trends</h3>
                <canvas id="usersChart" class="w-full h-64"></canvas>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Pickups Completed</h3>
                <canvas id="pickupsChart" class="w-full h-64"></canvas>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Recent Issues</h3>
            @if(isset($recentIssues) && count($recentIssues))
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentIssues as $issue)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $issue->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $issue->user->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $issue->issue_type ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        {{ $issue->status == 'resolved' ? 'bg-green-100 text-green-800' : ($issue->status == 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($issue->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No recent issues found.</p>
            @endif
        </div>

    </div>
</section>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctxUsers = document.getElementById('usersChart').getContext('2d');
    const usersChart = new Chart(ctxUsers, {
        type: 'line',
        data: {
            labels: @json($chartUsersLabels ?? []),
            datasets: [{
                label: 'New Users',
                data: @json($chartUsersData ?? []),
                borderColor: 'rgb(37, 99, 235)',
                backgroundColor: 'rgba(37, 99, 235, 0.2)',
                tension: 0.3,
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    const ctxPickups = document.getElementById('pickupsChart').getContext('2d');
    const pickupsChart = new Chart(ctxPickups, {
        type: 'bar',
        data: {
            labels: @json($chartPickupsLabels ?? []),
            datasets: [{
                label: 'Completed Pickups',
                data: @json($chartPickupsData ?? []),
                backgroundColor: 'rgba(34, 197, 94, 0.7)',
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
@endsection
