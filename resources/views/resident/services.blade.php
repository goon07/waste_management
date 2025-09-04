@extends('layouts.app')

@section('content')
<div x-data="{ requestPickupOpen: false, reportIssueOpen: false }">
    <!-- Main Section -->
    <section class="py-12 bg-gray-50 min-h-screen">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Resident Services</h2>
                <div class="space-x-4">
                    <button 
                        @click="requestPickupOpen = true" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                        Request Pickup
                    </button>
                    <button 
                        @click="reportIssueOpen = true" 
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">
                        Report Issue
                    </button>
                </div>
            </div>

            <!-- Page Content -->
                       <!-- Waste Collection Section -->
            <div x-data="{ activeTab: 'schedule', requestPickupOpen: false, reportIssueOpen: false }" x-init="console.log('Waste Collection x-data initialized')" class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Waste Collection</h3>
                <!-- Tabs -->
                <div class="flex border-b border-gray-200 mb-4">
                    <button @click="activeTab = 'schedule'" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 border-b-2" :class="{ 'border-green-600 text-green-600': activeTab === 'schedule' }" aria-selected="activeTab === 'schedule'" role="tab">Schedule</button>
                    <button @click="activeTab = 'history'" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 border-b-2" :class="{ 'border-green-600 text-green-600': activeTab === 'history' }" aria-selected="activeTab === 'history'" role="tab">History</button>
                </div>

                <!-- Schedule Tab -->
                <div x-show="activeTab === 'schedule'" x-cloak role="tabpanel">
                    @if ($residency && $residency->waste_collection_frequency)
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <p class="text-gray-600 mb-2"><strong>Frequency:</strong> {{ ucfirst($residency->waste_collection_frequency) }}</p>
                                <p class="text-gray-600 mb-2"><strong>Next Pickup:</strong> {{ $nextPickup ? $nextPickup->format('Y-m-d') : 'Not scheduled' }}</p>
                                <p class="text-gray-600 mb-2"><strong>Last Pickup:</strong> {{ $lastPickup ? $lastPickup->completed_date->format('Y-m-d') : 'No previous pickups' }}</p>
                                @if ($residency->latitude && $residency->longitude)
                                    <p class="text-gray-600"><strong>Location:</strong> Lat {{ $residency->latitude }}, Lon {{ $residency->longitude }}</p>
                                @endif
                            </div>
                            <div id="map" class="h-64 w-full rounded-lg" data-lat="{{ $residency->latitude ?? -26.2041 }}" data-lon="{{ $residency->longitude ?? 28.0473 }}"></div>
                        </div>
                    @else
                        <p class="text-gray-600">No pickup schedule defined. Update your residency details in the <a href="{{ route('resident.profile') }}" class="text-green-600 hover:underline">profile section</a>.</p>
                    @endif
                </div>

                <!-- History Tab -->
                <div x-show="activeTab === 'history'" x-cloak role="tabpanel">
                    @if ($collections->isEmpty())
                        <p class="text-gray-600">No pickup history available.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste Type</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed Date</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collector Company</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feedback</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($collections as $collection)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $collection->status == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ ucfirst($collection->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">{{ $collection->wasteType->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">{{ $collection->scheduled_date ? $collection->scheduled_date->format('Y-m-d') : 'N/A' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">{{ $collection->completed_date ? $collection->completed_date->format('Y-m-d') : 'N/A' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">{{ $collection->collectorCompany->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">{{ $collection->rating ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ Str::limit($collection->feedback ?? 'N/A', 50) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
    </section>

    <!-- Request Pickup Modal -->
    <div 
        x-show="requestPickupOpen" 
        x-cloak 
        x-transition.opacity 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
        @keydown.escape.window="requestPickupOpen = false"
        role="dialog" aria-labelledby="request-pickup-title">
        
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4 relative">
            <!-- Close Button -->
            <button 
                @click="requestPickupOpen = false" 
                class="absolute top-3 right-3 text-gray-500 hover:text-gray-700" 
                aria-label="Close modal">
                <i class="fas fa-times"></i>
            </button>

            <!-- Title -->
            <h3 id="request-pickup-title" class="text-lg font-bold text-gray-800 mb-4">Request a Pickup</h3>

            <!-- Form -->
            <form action="{{ route('resident.request_pickup') }}" method="POST" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <div>
                    <label for="scheduled_date" class="block text-gray-700 font-medium mb-2">Scheduled Date</label>
                    <input type="date" name="scheduled_date" id="scheduled_date" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                    @error('scheduled_date')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="collector_company_id" class="block text-gray-700 font-medium mb-2">Collector Company</label>
                    <select name="collector_company_id" id="collector_company_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        <option value="">Select Company</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('collector_company_id')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="waste_type" class="block text-gray-700 font-medium mb-2">Waste Type</label>
                    <select name="waste_type" id="waste_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        <option value="">Select Waste Type</option>
                        @foreach ($wasteTypes as $wasteType)
                            <option value="{{ $wasteType->id }}">{{ $wasteType->name }}</option>
                        @endforeach
                    </select>
                    @error('waste_type')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="requestPickupOpen = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold">Cancel</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold" x-bind:disabled="submitting">Request Pickup</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Issue Modal -->
    <div 
        x-show="reportIssueOpen" 
        x-cloak 
        x-transition.opacity 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50"
        @keydown.escape.window="reportIssueOpen = false"
        role="dialog" aria-labelledby="report-issue-title">
        
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4 relative">
            <!-- Close Button -->
            <button 
                @click="reportIssueOpen = false" 
                class="absolute top-3 right-3 text-gray-500 hover:text-gray-700" 
                aria-label="Close modal">
                <i class="fas fa-times"></i>
            </button>

            <!-- Title -->
            <h3 id="report-issue-title" class="text-lg font-bold text-gray-800 mb-4">Report an Issue</h3>

            <!-- Form -->
            <form action="{{ route('resident.report_issue') }}" method="POST" class="space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                <div>
                    <label for="issue_type" class="block text-gray-700 font-medium mb-2">Issue Type</label>
                    <select name="issue_type" id="issue_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        <option value="">Select Issue Type</option>
                        <option value="Missed Pickup">Missed Pickup</option>
                        <option value="Damaged Bin">Damaged Bin</option>
                        <option value="Other">Other</option>
                    </select>
                    @error('issue_type')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-gray-700 font-medium mb-2">Issue Description</label>
                    <textarea name="description" id="description" rows="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required></textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="council_id" class="block text-gray-700 font-medium mb-2">Council</label>
                    <select name="council_id" id="council_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        <option value="">Select Council</option>
                        @foreach ($councils as $council)
                            <option value="{{ $council->id }}">{{ $council->name }}</option>
                        @endforeach
                    </select>
                    @error('council_id')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="collector_company_id" class="block text-gray-700 font-medium mb-2">Collector Company (Optional)</label>
                    <select name="collector_company_id" id="collector_company_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="">Select Company</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('collector_company_id')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="reportIssueOpen = false" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-semibold">Cancel</button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold" x-bind:disabled="submitting">Report Issue</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
 <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            console.log('Alpine loaded:', typeof Alpine !== 'undefined');
            document.addEventListener('DOMContentLoaded', () => {
                // Initialize Leaflet map
                const mapElement = document.getElementById('map');
                if (mapElement) {
                    const lat = parseFloat(mapElement.dataset.lat);
                    const lon = parseFloat(mapElement.dataset.lon);
                    const map = L.map('map').setView([lat, lon], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(map);
                    L.marker([lat, lon]).addTo(map).bindPopup('Your Location').openPopup();
                }

                // Client-side form validation for Request Pickup
                window.validateForm = function(event) {
                    const form = event.target;
                    const scheduledDate = form.querySelector('#scheduled_date').value;
                    const today = new Date().toISOString().split('T')[0];
                    if (scheduledDate <= today) {
                        event.preventDefault();
                        alert('Scheduled date must be in the future.');
                        form.querySelector('#scheduled_date').focus();
                        form.closest('[x-data]').__x.$data.submitting = false;
                    }
                };
            });
        </script>
<style>
    [x-cloak] { display: none !important; }
    [x-transition.opacity] {
        transition: opacity 0.3s ease;
    }
    [x-transition.opacity][x-show="false"] {
        opacity: 0;
    }
    [x-transition.opacity][x-show="true"] {
        opacity: 1;
    }
</style>
@endpush
@endsection
