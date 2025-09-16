@extends('layouts.app')

@section('content')
<section class="py-6 bg-gray-50 border-b mb-6">
    <div class="container mx-auto px-4 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800">{{ $company->name ?? 'Company' }} Dashboard</h1>
        <div class="text-gray-700">Logged in as: <strong>{{ $user->name }}</strong></div>
    </div>
</section>

<!-- Schedule Collection Button -->
<div class="mb-6">
    <button id="openScheduleModal" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Schedule Collection
    </button>
</div>

<!-- Schedule Collection Modal -->
<div id="scheduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
        <h2 class="text-xl font-bold mb-4">Schedule Collection</h2>
        <form action="{{ route('management.collections.schedule') }}" method="POST" id="scheduleForm">
            @csrf

            <!-- Schedule For -->
            <label class="block mb-2 font-semibold">Schedule For</label>
            <select name="schedule_for" id="scheduleFor" class="w-full border rounded px-3 py-2 mb-4" required>
                <option value="">Select</option>
                <option value="individual">Individual Resident</option>
                <option value="area">All Residents in Area</option>
                <option value="all">All Residents in Company</option>
            </select>

            <!-- Individual Resident Select -->
            <div id="individualResidentDiv" class="mb-4 hidden">
                <label class="block mb-2 font-semibold">Select Resident</label>
                <select name="resident_id" class="w-full border rounded px-3 py-2">
                    <option value="">Select Resident</option>
                    @foreach($residents as $resident)
                        <option value="{{ $resident->id }}">{{ $resident->name }} ({{ optional($resident->residency->area)->name ?? 'No Area' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Area Select -->
            <div id="areaDiv" class="mb-4 hidden">
                <label class="block mb-2 font-semibold">Select Area</label>
                <select name="area_id" class="w-full border rounded px-3 py-2">
                    <option value="">Select Area</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Collector Select -->
            <label class="block mb-2 font-semibold">Assign Collector</label>
            <select name="collector_id" class="w-full border rounded px-3 py-2 mb-4" required>
                <option value="">Select Collector</option>
                @foreach($collectors as $collector)
                    <option value="{{ $collector->id }}">{{ $collector->name }}</option>
                @endforeach
            </select>

            <!-- Waste Type Select -->
            <label class="block mb-2 font-semibold">Waste Type</label>
            <select name="waste_type" class="w-full border rounded px-3 py-2 mb-4" required>
                <option value="">Select Waste Type</option>
                @foreach($wasteTypes as $wasteType)
                    <option value="{{ $wasteType->id }}">{{ $wasteType->name }}</option>
                @endforeach
            </select>

            <!-- Schedule Type -->
            <label class="block mb-2 font-semibold">Schedule Type</label>
            <select name="schedule_type" id="scheduleType" class="w-full border rounded px-3 py-2 mb-4" required>
                <option value="">Select Schedule Type</option>
                <option value="specific_date">Specific Date</option>
                <option value="weekly">Weekly</option>
                <option value="biweekly">Bi-Weekly</option>
                <option value="monthly">Monthly</option>
            </select>

            <!-- Specific Date -->
            <div id="specificDateDiv" class="mb-4 hidden">
                <label class="block mb-2 font-semibold">Select Date</label>
                <input type="date" name="specific_date" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" class="w-full border rounded px-3 py-2">
            </div>

            <!-- Weekly Day -->
            <div id="weeklyDayDiv" class="mb-4 hidden">
                <label class="block mb-2 font-semibold">Select Day of Week</label>
                <select name="weekly_day" class="w-full border rounded px-3 py-2">
                    @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                        <option value="{{ $day }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Biweekly Day -->
            <div id="biweeklyDayDiv" class="mb-4 hidden">
                <label class="block mb-2 font-semibold">Select Day of Bi-Weekly Schedule</label>
                <select name="biweekly_day" class="w-full border rounded px-3 py-2">
                    @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                        <option value="{{ $day }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Monthly Day -->
            <div id="monthlyDayDiv" class="mb-4 hidden">
                <label class="block mb-2 font-semibold">Select Day of Month (1-28)</label>
                <input type="number" name="monthly_day" min="1" max="28" class="w-full border rounded px-3 py-2" placeholder="Day of month">
            </div>

            <div class="flex justify-end space-x-4">
                <button type="button" id="closeScheduleModal" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Schedule</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Script -->
<script>
    const scheduleModal = document.getElementById('scheduleModal');
    const openModalBtn = document.getElementById('openScheduleModal');
    const closeModalBtn = document.getElementById('closeScheduleModal');

    const scheduleForSelect = document.getElementById('scheduleFor');
    const individualResidentDiv = document.getElementById('individualResidentDiv');
    const areaDiv = document.getElementById('areaDiv');

    const scheduleTypeSelect = document.getElementById('scheduleType');
    const specificDateDiv = document.getElementById('specificDateDiv');
    const weeklyDayDiv = document.getElementById('weeklyDayDiv');
    const biweeklyDayDiv = document.getElementById('biweeklyDayDiv');
    const monthlyDayDiv = document.getElementById('monthlyDayDiv');

    openModalBtn.addEventListener('click', () => {
        scheduleModal.classList.remove('hidden');
    });

    closeModalBtn.addEventListener('click', () => {
        scheduleModal.classList.add('hidden');
        resetForm();
    });

    scheduleForSelect.addEventListener('change', () => {
        const val = scheduleForSelect.value;
        individualResidentDiv.classList.toggle('hidden', val !== 'individual');
        areaDiv.classList.toggle('hidden', val !== 'area');
    });

    scheduleTypeSelect.addEventListener('change', () => {
        const val = scheduleTypeSelect.value;
        specificDateDiv.classList.toggle('hidden', val !== 'specific_date');
        weeklyDayDiv.classList.toggle('hidden', val !== 'weekly');
        biweeklyDayDiv.classList.toggle('hidden', val !== 'biweekly');
        monthlyDayDiv.classList.toggle('hidden', val !== 'monthly');
    });

    function resetForm() {
        document.getElementById('scheduleForm').reset();
        individualResidentDiv.classList.add('hidden');
        areaDiv.classList.add('hidden');
        specificDateDiv.classList.add('hidden');
        weeklyDayDiv.classList.add('hidden');
        biweeklyDayDiv.classList.add('hidden');
        monthlyDayDiv.classList.add('hidden');
    }
</script>

<div class="container mx-auto px-4">

    <!-- Past Collections -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold mb-4">Past Collections (Completed)</h3>
        @if($pastCollections->isEmpty())
            <p>No past collections found.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded shadow">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2">Resident</th>
                            <th class="px-4 py-2">Area</th>
                            <th class="px-4 py-2">Collector</th>
                            <th class="px-4 py-2">Waste Type</th>
                            <th class="px-4 py-2">Completed Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pastCollections as $schedule)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $schedule->collection->user->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ optional($schedule->collection->user->residency->area)->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $schedule->collector->name ?? 'Unassigned' }}</td>
                                <td class="px-4 py-2">{{ ucfirst(optional($schedule->collection->wasteType)->name ?? 'N/A') }}</td>
                                <td class="px-4 py-2">{{ optional($schedule->scheduled_date)->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Next Scheduled Collections -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold mb-4">Next Scheduled Collections</h3>
        @if($nextScheduledCollections->isEmpty())
            <p>No upcoming scheduled collections.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded shadow">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2">Resident</th>
                            <th class="px-4 py-2">Area</th>
                            <th class="px-4 py-2">Collector</th>
                            <th class="px-4 py-2">Waste Type</th>
                            <th class="px-4 py-2">Scheduled Date</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nextScheduledCollections as $schedule)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $schedule->collection->user->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ optional($schedule->collection->user->residency->area)->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $schedule->collector->name ?? 'Unassigned' }}</td>
                                <td class="px-4 py-2">{{ ucfirst(optional($schedule->collection->wasteType)->name ?? 'N/A') }}</td>
                                <td class="px-4 py-2">{{ optional($schedule->scheduled_date)->format('Y-m-d') ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('management.collection_schedules.edit', $schedule->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Residents Without Recent Collections -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold mb-4">Residents Without Recent Collections</h3>
        @if($residentsWithoutRecentCollections->isEmpty())
            <p>All residents have recent collections.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded shadow">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2">Resident</th>
                            <th class="px-4 py-2">Area</th>
                            <th class="px-4 py-2">Assigned Collector</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($residentsWithoutRecentCollections as $resident)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $resident->name }}</td>
                                <td class="px-4 py-2">{{ optional($resident->residency->area)->name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">
                                    @php
                                        $assignment = $resident->collectorResidentAssignments()->with('collector')->first();
                                    @endphp
                                    {{ $assignment->collector->name ?? 'Unassigned' }}
                                </td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('management.collections.create', ['resident_id' => $resident->id]) }}" 
                                       class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                       Schedule Collection
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection