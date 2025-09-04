@extends('layouts.app')

@section('content')
    <section class="py-12 bg-gray-50 min-h-screen">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-4xl font-bold text-gray-900">Collector Dashboard</h2>
                <a href="{{ route('logout') }}" class="text-red-600 hover:text-red-800 font-medium">Logout</a>
            </div>
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            <div class="tabs">
                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex space-x-4">
                        <button class="tab-button border-b-2 border-green-600 text-green-600 px-4 py-2 text-sm font-semibold" data-tab="assigned">Assigned Pickups</button>
                        <button class="tab-button border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 px-4 py-2 text-sm font-semibold" data-tab="completed">Completed Pickups</button>
                        <button class="tab-button border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 px-4 py-2 text-sm font-semibold" data-tab="all">All Pickups</button>
                        <button class="tab-button border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 px-4 py-2 text-sm font-semibold" data-tab="waste-guide">Waste Guide</button>
                    </nav>
                </div>
                <div class="tab-content">
                    <!-- Assigned Pickups Tab -->
                    <div id="assigned" class="tab-pane">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Assigned Pickups</h3>
                            @if ($assignedPickups->isEmpty())
                                <p class="text-gray-600">No assigned pickups found.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resident</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($assignedPickups as $pickup)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pickup->user->name ?? 'Unknown' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pickup->waste_type ?? 'Unknown' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pickup->scheduled_date ? \Carbon\Carbon::parse($pickup->scheduled_date)->toDateString() : 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($pickup->status) }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <a href="{{ route('collector.confirm-pickup', $pickup->id) }}" class="text-green-600 hover:underline">Confirm</a>
                                                        <a href="{{ route('collector.report-issue', $pickup->id) }}" class="text-red-600 hover:underline ml-4">Report Issue</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Completed Pickups Tab -->
                    <div id="completed" class="tab-pane hidden">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Completed Pickups</h3>
                            @if ($completedPickups->isEmpty())
                                <p class="text-gray-600">No completed pickups found.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resident</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($completedPickups as $pickup)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pickup->user->name ?? 'Unknown' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pickup->waste_type ?? 'Unknown' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pickup->scheduled_date ? \Carbon\Carbon::parse($pickup->scheduled_date)->toDateString() : 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($pickup->status) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- All Pickups Tab -->
                    <div id="all" class="tab-pane hidden">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">All Pickups</h3>
                            @if ($allPickups->isEmpty())
                                <p class="text-gray-600">No pickups found.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resident</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste Type</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($allPickups as $pickup)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pickup->user->name ?? 'Unknown' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pickup->waste_type ?? 'Unknown' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pickup->scheduled_date ? \Carbon\Carbon::parse($pickup->scheduled_date)->toDateString() : 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($pickup->status) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Waste Guide Tab -->
                    <div id="waste-guide" class="tab-pane hidden">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Waste Guide</h3>
                            @if ($wasteGuide->isEmpty())
                                <p class="text-gray-600">No waste guide available.</p>
                            @else
                                <div class="space-y-4">
                                    @foreach ($wasteGuide as $guide)
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-800">{{ $guide->title }}</h4>
                                            <p class="text-gray-600">{{ $guide->description }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                const tab = button.getAttribute('data-tab');
                document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.add('hidden'));
                document.getElementById(tab).classList.remove('hidden');
                document.querySelectorAll('.tab-button').forEach(btn => {
                    btn.classList.remove('border-green-600', 'text-green-600');
                    btn.classList.add('border-transparent', 'text-gray-600', 'hover:text-gray-800', 'hover:border-gray-300');
                });
                button.classList.remove('border-transparent', 'text-gray-600', 'hover:text-gray-800', 'hover:border-gray-300');
                button.classList.add('border-green-600', 'text-green-600');
            });
        });
    </script>
@endsection