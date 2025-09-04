@extends('layouts.app')

@section('content')
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-semibold text-gray-900">Waste Collection Management</h2>
                
            </div>

            <!-- Session Messages -->
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-600 text-green-800 p-4 mb-6 rounded-lg" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-600 text-red-800 p-4 mb-6 rounded-lg" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filters -->
            <form method="GET" action="{{ route('council.pickups') }}" class="mb-6 bg-white rounded-xl shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" class="mt-1 px-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                  
                    <div>
                        <label for="collector_id" class="block text-sm font-medium text-gray-700">Collector</label>
                        <select name="collector_id" id="collector_id" class="mt-1 px-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">All Collectors</option>
                            @foreach ($collectors as $collector)
                                <option value="{{ $collector->id }}" {{ request('collector_id') == $collector->id ? 'selected' : '' }}>{{ $collector->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_range" class="block text-sm font-medium text-gray-700">Date Range</label>
                        <input type="text" name="date_range" id="date_range" value="{{ request('date_range') }}" class="mt-1 px-4 py-2 border border-gray-300 rounded-lg w-full focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="Select date range">
                    </div>
                </div>
                <div class="mt-4 flex space-x-4">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">Apply Filters</button>
                    <a href="{{ route('council.pickups') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200">Clear Filters</a>
                </div>
            </form>

            <!-- Bulk Schedule Form -->
            <form id="bulk-schedule-form" action="" method="POST" class="mb-6 bg-white rounded-xl shadow-sm p-6">
                @csrf
                <div class="flex items-center space-x-4">
                    <select name="collector_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Select Collector for Bulk Schedule</option>
                        @foreach ($collectors as $collector)
                            <option value="{{ $collector->id }}">{{ $collector->name }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="scheduled_date" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}" required>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">Bulk Schedule</button>
                </div>
                @error('collector_id')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
                @error('scheduled_date')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </form>

            <!-- Pickups Table -->
            <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" x-data @click="$refs.checkboxes.forEach(checkbox => checkbox.checked = $event.target.checked)" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('council.pickups', array_merge(request()->query(), ['sort' => 'user_name', 'direction' => request('sort') == 'user_name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">User {{ request('sort') == 'user_name' ? (request('direction') == 'asc' ? '↑' : '↓') : '' }}</a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('council.pickups', array_merge(request()->query(), ['sort' => 'waste_type', 'direction' => request('sort') == 'waste_type' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">Waste Type {{ request('sort') == 'waste_type' ? (request('direction') == 'asc' ? '↑' : '↓') : '' }}</a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('council.pickups', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">Status {{ request('sort') == 'status' ? (request('direction') == 'asc' ? '↑' : '↓') : '' }}</a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('council.pickups', array_merge(request()->query(), ['sort' => 'scheduled_date', 'direction' => request('sort') == 'scheduled_date' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">Scheduled Date {{ request('sort') == 'scheduled_date' ? (request('direction') == 'asc' ? '↑' : '↓') : '' }}</a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ route('council.pickups', array_merge(request()->query(), ['sort' => 'collector_name', 'direction' => request('sort') == 'collector_name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}">Collector {{ request('sort') == 'collector_name' ? (request('direction') == 'asc' ? '↑' : '↓') : '' }}</a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feedback</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if ($pickups->isEmpty())
                            <tr>
                                <td colspan="10" class="px-6 py-4 text-center text-gray-600">No pickups found.</td>
                            </tr>
                        @else
                            @foreach ($pickups as $pickup)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($pickup->status == 'pending')
                                            <input type="checkbox" name="pickup_ids[]" value="{{ $pickup->id }}" form="bulk-schedule-form" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" x-ref="checkboxes">
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $pickup->user->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($pickup->wasteType->name ?? $pickup->waste_type) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pickup->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : ($pickup->status == 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                            {{ ucfirst($pickup->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $pickup->scheduled_date ? \Carbon\Carbon::parse($pickup->scheduled_date)->format('Y-m-d H:i') : ($pickup->status == 'pending' ? \Carbon\Carbon::parse($pickup->created_at)->format('Y-m-d H:i') : 'N/A') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $pickup->collector ? $pickup->collector->name : 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $pickup->rating ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $pickup->feedback ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $pickup->user->address ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if ($pickup->status == 'pending')
                                            <form action="{{ route('council.pickup.schedule', $pickup->id) }}" method="POST" class="inline">
                                                @csrf
                                                <div class="flex items-center space-x-2">
                                                    <select name="collector_id" class="px-2 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                                        <option value="">Select Collector</option>
                                                        @foreach ($collectors as $collector)
                                                            <option value="{{ $collector->id }}">{{ $collector->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="date" name="scheduled_date" class="px-2 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}" required>
                                                    <button type="submit" class="bg-green-600 text-white px-2 py-1 rounded-lg hover:bg-green-700 transition duration-200">Schedule</button>
                                                </div>
                                            </form>
                                        @elseif ($pickup->status == 'scheduled')
                                            <div class="flex space-x-2">
                                                <a href="{{ route('council.pickup.edit', $pickup->id) }}" class="bg-blue-600 text-white px-2 py-1 rounded-lg hover:bg-blue-700 transition duration-200">Edit</a>
                                                <form action="{{ route('council.pickup.cancel', $pickup->id) }}" method="POST" x-data="{ confirmCancel: false }">
                                                    @csrf
                                                    <button type="button" @click="confirmCancel = true" class="bg-red-600 text-white px-2 py-1 rounded-lg hover:bg-red-700 transition duration-200" x-show="!confirmCancel">Cancel</button>
                                                    <div x-show="confirmCancel" class="flex space-x-2">
                                                        <button type="submit" class="bg-red-600 text-white px-2 py-1 rounded-lg hover:bg-red-700 transition duration-200">Confirm</button>
                                                        <button type="button" @click="confirmCancel = false" class="bg-gray-600 text-white px-2 py-1 rounded-lg hover:bg-gray-700 transition duration-200">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                <!-- Pagination -->
                <div class="px-6 py-4">
                    {{ $pickups->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            </div>

            <!-- Map Link -->
            <a href="{{ route('map') }}" class="fixed bottom-6 right-6 bg-green-600 hover:bg-green-700 text-white p-4 rounded-full shadow-lg transition duration-200" title="View Map">
                <i class="fas fa-map text-xl"></i>
            </a>
        </div>
    </section>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script>
            flatpickr('#date_range', {
                mode: 'range',
                dateFormat: 'Y-m-d',
                minDate: '2020-01-01',
                maxDate: 'today',
            });
        </script>
    @endpush
@endsection