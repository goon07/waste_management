@extends('layouts.app')

@section('content')
<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
        z-index: 1000;
        overflow-y: auto; /* Enable scrolling for the modal container */
    }
    .modal.show {
        display: flex;
    }
    .modal-content {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 32rem;
        max-height: 80vh; /* Limit height to 80% of viewport height */
        overflow-y: auto; /* Enable vertical scrolling for content */
        padding: 1.5rem;
        position: relative;
        margin: 1rem; /* Add margin for small screens */
    }
    /* Ensure modal is centered on small screens */
    @media (max-height: 600px) {
        .modal-content {
            max-height: 90vh; /* Slightly more height on very small screens */
        }
    }
</style>

<section class="py-6 bg-gradient-to-r from-blue-50 to-gray-50 border-b mb-6">
    <div class="container mx-auto px-4 flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800">{{ $company->name ?? 'Company' }} Dashboard</h1>
        <div class="text-gray-700">Logged in as: <strong>{{ $user->name }}</strong></div>
    </div>
</section>

<div class="container mx-auto px-4">
    <!-- Action Buttons -->
    <div class="flex justify-between mb-6 space-x-4">
        <button id="openScheduleModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Schedule Collection</button>
        <button id="openAssignModal" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">Assign Collectors</button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold mb-4">Filters</h3>
        <form method="GET" action="{{ route('management.dashboard') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block mb-2 font-semibold">Date From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-2 font-semibold">Date To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block mb-2 font-semibold">Area</label>
                <select name="area_id" class="w-full border rounded px-3 py-2">
                    <option value="">All Areas</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" {{ $areaId == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-2 font-semibold">Waste Type</label>
                <select name="waste_type_id" class="w-full border rounded px-3 py-2">
                    <option value="">All Waste Types</option>
                    @foreach($wasteTypes as $wasteType)
                        <option value="{{ $wasteType->id }}" {{ $wasteTypeId == $wasteType->id ? 'selected' : '' }}>{{ $wasteType->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-2 font-semibold">Collector</label>
                <select name="collector_id" class="w-full border rounded px-3 py-2">
                    <option value="">All Collectors</option>
                    @foreach($collectors as $collector)
                        <option value="{{ $collector->id }}" {{ $collectorId == $collector->id ? 'selected' : '' }}>{{ $collector->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-2 font-semibold">Serviced Period (days)</label>
                <input type="number" name="serviced_period" value="{{ $servicedPeriod }}" min="1" max="365" class="w-full border rounded px-3 py-2" placeholder="e.g., 30">
            </div>
            <div class="col-span-3">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Apply Filters</button>
                <a href="{{ route('management.dashboard') }}" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">Clear Filters</a>
            </div>
        </form>
    </div>

    <!-- Schedule Collection Modal -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4">Schedule Collection</h2>
            <form action="{{ route('management.collections.schedule') }}" method="POST" id="scheduleForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block mb-2 font-semibold">Schedule For</label>
                        <select name="schedule_for" id="scheduleFor" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select</option>
                            <option value="individual">Individual Resident</option>
                            <option value="area">All Residents in Area</option>
                            <option value="all">All Residents in Company</option>
                        </select>
                    </div>
                    <div id="individualResidentDiv" class="hidden">
                        <label class="block mb-2 font-semibold">Select Resident</label>
                        <select name="resident_id" class="w-full border rounded px-3 py-2 select2">
                            <option value="">Select Resident</option>
                            @foreach($residents as $resident)
                                <option value="{{ $resident->id }}">{{ $resident->name }} ({{ optional($resident->residency->area)->name ?? 'No Area' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="areaDiv" class="hidden">
                        <label class="block mb-2 font-semibold">Select Area</label>
                        <select name="area_id" class="w-full border rounded px-3 py-2">
                            <option value="">Select Area</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Assign Collector</label>
                        <select name="collector_id" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select Collector</option>
                            @foreach($collectors as $collector)
                                <option value="{{ $collector->id }}">{{ $collector->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Waste Type</label>
                        <select name="waste_type_id" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select Waste Type</option>
                            @foreach($wasteTypes as $wasteType)
                                <option value="{{ $wasteType->id }}">{{ $wasteType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Schedule Type</label>
                        <select name="schedule_type" id="scheduleType" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select Schedule Type</option>
                            <option value="one_time">One-Time</option>
                            <option value="weekly">Weekly</option>
                            <option value="biweekly">Bi-Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div id="specificDateDiv" class="hidden">
                        <label class="block mb-2 font-semibold">Select Date</label>
                        <input type="date" name="specific_date" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" max="{{ \Carbon\Carbon::today()->addYear()->format('Y-m-d') }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div id="recurringDayDiv" class="hidden">
                        <label class="block mb-2 font-semibold">Select Day of Week</label>
                        <select name="recurring_day" class="w-full border rounded px-3 py-2">
                            @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                                <option value="{{ $day }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="monthlyDayDiv" class="hidden">
                        <label class="block mb-2 font-semibold">Select Day of Month (1-31)</label>
                        <input type="number" name="monthly_day" min="1" max="31" class="w-full border rounded px-3 py-2" placeholder="Day of month">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Start Date</label>
                        <input type="date" name="start_date" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" max="{{ \Carbon\Carbon::today()->addYear()->format('Y-m-d') }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">End Date (Optional)</label>
                        <input type="date" name="end_date" min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}" max="{{ \Carbon\Carbon::today()->addYears(2)->format('Y-m-d') }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" id="closeScheduleModal" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">Cancel</button>
                        <button type="submit" id="submitSchedule" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Schedule</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div id="editScheduleModal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4">Edit Schedule</h2>
            <form action="{{ route('management.collection_schedules.update', ['collection_schedule' => ':schedule_id']) }}" method="POST" id="editScheduleForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="schedule_id" id="editScheduleId">
                <div class="space-y-4">
                    <div>
                        <label class="block mb-2 font-semibold">Resident</label>
                        <input type="text" id="editResidentName" class="w-full border rounded px-3 py-2" readonly>
                        <input type="hidden" name="resident_id" id="editResidentId">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Assign Collector</label>
                        <select name="collector_id" id="editCollectorId" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select Collector</option>
                            @foreach($collectors as $collector)
                                <option value="{{ $collector->id }}">{{ $collector->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Waste Type</label>
                        <select name="waste_type_id" id="editWasteType" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select Waste Type</option>
                            @foreach($wasteTypes as $wasteType)
                                <option value="{{ $wasteType->id }}">{{ $wasteType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Schedule Type</label>
                        <select name="schedule_type" id="editScheduleType" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select Schedule Type</option>
                            <option value="one_time">One-Time</option>
                            <option value="weekly">Weekly</option>
                            <option value="biweekly">Bi-Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div id="editSpecificDateDiv" class="hidden">
                        <label class="block mb-2 font-semibold">Select Date</label>
                        <input type="date" name="specific_date" id="editSpecificDate" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" max="{{ \Carbon\Carbon::today()->addYear()->format('Y-m-d') }}" class="w-full border rounded px-3 py-2">
                    </div>
                    <div id="editRecurringDayDiv" class="hidden">
                        <label class="block mb-2 font-semibold">Select Day of Week</label>
                        <select name="recurring_day" id="editRecurringDay" class="w-full border rounded px-3 py-2">
                            @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                                <option value="{{ $day }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="editMonthlyDayDiv" class="hidden">
                        <label class="block mb-2 font-semibold">Select Day of Month (1-31)</label>
                        <input type="number" name="monthly_day" id="editMonthlyDay" min="1" max="31" class="w-full border rounded px-3 py-2" placeholder="Day of month">
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" id="closeEditScheduleModal" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">Cancel</button>
                        <button type="submit" id="submitEditSchedule" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Collectors Modal -->
    <div id="assignModal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4">Assign Collectors</h2>
            <form action="{{ route('management.collectors.assign') }}" method="POST" id="assignForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block mb-2 font-semibold">Assign To</label>
                        <select name="assignment_type" id="assignTo" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select</option>
                            <option value="individual">Individual Resident</option>
                            <option value="area">All Residents in Area</option>
                            <option value="bulk">All Residents in Company</option>
                        </select>
                    </div>
                    <div id="assignIndividualResidentDiv" class="hidden">
                        <label class="block mb-2 font-semibold">Select Resident</label>
                        <select name="resident_ids[]" multiple class="w-full border rounded px-3 py-2 select2">
                            <option value="">Select Resident</option>
                            @foreach($residents as $resident)
                                <option value="{{ $resident->id }}">{{ $resident->name }} ({{ optional($resident->residency->area)->name ?? 'No Area' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="assignAreaDiv" class="hidden">
                        <label class="block mb-2 font-semibold">Select Area</label>
                        <select name="area_id" class="w-full border rounded px-3 py-2">
                            <option value="">Select Area</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold">Assign Collector</label>
                        <select name="collector_id" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select Collector</option>
                            @foreach($collectors as $collector)
                                <option value="{{ $collector->id }}">{{ $collector->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" id="closeAssignModal" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">Cancel</button>
                        <button type="submit" id="submitAssign" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">Assign</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Unified Table -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold mb-4">Collections and Scheduling</h3>
        @if($combinedData->isEmpty())
            <p class="text-gray-600">No records found.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded shadow">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left">Type</th>
                            <th class="px-4 py-2 text-left">Resident</th>
                            <th class="px-4 py-2 text-left">Area</th>
                            <th class="px-4 py-2 text-left">Collector</th>
                            <th class="px-4 py-2 text-left">Waste Type</th>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Schedule Type</th>
                            <th class="px-4 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($combinedData as $item)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2">
                                    @if($item->type == 'completed')
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Completed</span>
                                    @elseif($item->type == 'scheduled')
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">Scheduled</span>
                                    @else
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">Needs Scheduling</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2">{{ $item->resident_name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $item->area_name ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $item->collector_name ?? 'Unassigned' }}</td>
                                <td class="px-4 py-2">{{ ucfirst($item->waste_type_name ?? 'N/A') }}</td>
                                <td class="px-4 py-2">{{ $item->scheduled_date ? \Carbon\Carbon::parse($item->scheduled_date)->format('Y-m-d') : '-' }}</td>
                                <td class="px-4 py-2">{{ ucfirst($item->schedule_type ?? 'N/A') }}</td>
                                <td class="px-4 py-2">
                                    @if($item->type == 'scheduled')
                                        <button class="edit-schedule-btn px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition" 
                                                data-schedule-id="{{ $item->schedule_id }}"
                                                data-resident-name="{{ $item->resident_name }}"
                                                data-resident-id="{{ $item->resident_id }}"
                                                data-collector-id="{{ $item->assigned_collector_id ?? '' }}"
                                                data-waste-type="{{ $item->waste_type_id ?? '' }}"
                                                data-schedule-type="{{ $item->schedule_type }}"
                                                data-specific-date="{{ $item->scheduled_date ? \Carbon\Carbon::parse($item->scheduled_date)->format('Y-m-d') : '' }}"
                                                data-recurring-day="{{ $item->recurring_day }}"
                                                data-monthly-day="{{ $item->monthly_day }}">Edit</button>
                                        @if($item->schedule_type == 'one_time')
                                            <button class="complete-btn px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition" 
                                                    data-schedule-id="{{ $item->schedule_id }}">Complete</button>
                                        @endif
                                    @elseif($item->type == 'needs_scheduling')
                                        <button class="schedule-btn px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition" 
                                                data-resident-id="{{ $item->resident_id }}">Schedule</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $combinedData->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- External Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Select2
        $('.select2').select2({
            placeholder: "Select Resident",
            allowClear: true
        });

        // Initialize Flatpickr
        flatpickr('input[type=date]', {
            minDate: 'today',
            maxDate: new Date().fp_incr(365),
            dateFormat: 'Y-m-d',
        });

        // Schedule Modal
        const scheduleModal = document.getElementById('scheduleModal');
        const openScheduleModalBtn = document.getElementById('openScheduleModal');
        const closeScheduleModalBtn = document.getElementById('closeScheduleModal');
        const scheduleForSelect = document.getElementById('scheduleFor');
        const individualResidentDiv = document.getElementById('individualResidentDiv');
        const areaDiv = document.getElementById('areaDiv');
        const scheduleTypeSelect = document.getElementById('scheduleType');
        const specificDateDiv = document.getElementById('specificDateDiv');
        const recurringDayDiv = document.getElementById('recurringDayDiv');
        const monthlyDayDiv = document.getElementById('monthlyDayDiv');
        const submitScheduleBtn = document.getElementById('submitSchedule');

        openScheduleModalBtn.addEventListener('click', () => {
            scheduleModal.classList.add('show');
        });

        closeScheduleModalBtn.addEventListener('click', () => {
            scheduleModal.classList.remove('show');
            resetScheduleForm();
        });

        scheduleForSelect.addEventListener('change', () => {
            const val = scheduleForSelect.value;
            individualResidentDiv.classList.toggle('hidden', val !== 'individual');
            areaDiv.classList.toggle('hidden', val !== 'area');
        });

        scheduleTypeSelect.addEventListener('change', () => {
            const val = scheduleTypeSelect.value;
            specificDateDiv.classList.toggle('hidden', val !== 'one_time');
            recurringDayDiv.classList.toggle('hidden', !['weekly', 'biweekly'].includes(val));
            monthlyDayDiv.classList.toggle('hidden', val !== 'monthly');
        });

        submitScheduleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const form = document.getElementById('scheduleForm');
            if (scheduleForSelect.value === 'individual' && !form.querySelector('[name="resident_id"]').value) {
                Swal.fire('Error', 'Please select a resident.', 'error');
            } else if (scheduleForSelect.value === 'area' && !form.querySelector('[name="area_id"]').value) {
                Swal.fire('Error', 'Please select an area.', 'error');
            } else {
                Swal.fire({
                    title: 'Confirm Schedule',
                    text: 'Are you sure you want to schedule this collection?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Schedule',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        submitScheduleBtn.disabled = true;
                        submitScheduleBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" /></svg> Scheduling...';
                        form.submit();
                    }
                });
            }
        });

        function resetScheduleForm() {
            document.getElementById('scheduleForm').reset();
            individualResidentDiv.classList.add('hidden');
            areaDiv.classList.add('hidden');
            specificDateDiv.classList.add('hidden');
            recurringDayDiv.classList.add('hidden');
            monthlyDayDiv.classList.add('hidden');
            $('.select2').val('').trigger('change');
            submitScheduleBtn.disabled = false;
            submitScheduleBtn.innerHTML = 'Schedule';
        }

        // Edit Schedule Modal
        const editScheduleModal = document.getElementById('editScheduleModal');
        const closeEditScheduleModalBtn = document.getElementById('closeEditScheduleModal');
        const editScheduleForm = document.getElementById('editScheduleForm');
        const editScheduleTypeSelect = document.getElementById('editScheduleType');
        const editSpecificDateDiv = document.getElementById('editSpecificDateDiv');
        const editRecurringDayDiv = document.getElementById('editRecurringDayDiv');
        const editMonthlyDayDiv = document.getElementById('editMonthlyDayDiv');
        const submitEditScheduleBtn = document.getElementById('submitEditSchedule');

        closeEditScheduleModalBtn.addEventListener('click', () => {
            editScheduleModal.classList.remove('show');
            resetEditScheduleForm();
        });

        editScheduleTypeSelect.addEventListener('change', () => {
            const val = editScheduleTypeSelect.value;
            editSpecificDateDiv.classList.toggle('hidden', val !== 'one_time');
            editRecurringDayDiv.classList.toggle('hidden', !['weekly', 'biweekly'].includes(val));
            editMonthlyDayDiv.classList.toggle('hidden', val !== 'monthly');
        });

        submitEditScheduleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const form = editScheduleForm;
            Swal.fire({
                title: 'Confirm Update',
                text: 'Are you sure you want to update this schedule?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Update',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    submitEditScheduleBtn.disabled = true;
                    submitEditScheduleBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" /></svg> Updating...';
                    const scheduleId = document.getElementById('editScheduleId').value;
                    form.action = form.action.replace(':schedule_id', scheduleId);
                    form.submit();
                }
            });
        });

        function resetEditScheduleForm() {
            editScheduleForm.reset();
            editSpecificDateDiv.classList.add('hidden');
            editRecurringDayDiv.classList.add('hidden');
            editMonthlyDayDiv.classList.add('hidden');
            submitEditScheduleBtn.disabled = false;
            submitEditScheduleBtn.innerHTML = 'Update';
        }

        // Complete Button
        document.querySelectorAll('.complete-btn').forEach(button => {
            button.addEventListener('click', () => {
                const scheduleId = button.dataset.scheduleId;
                Swal.fire({
                    title: 'Complete Collection',
                    html: `
                        <div class="space-y-4">
                            <div>
                                <label class="block mb-2 font-semibold">Feedback Rating (1-5)</label>
                                <input type="number" id="feedbackRating" min="1" max="5" class="w-full border rounded px-3 py-2" placeholder="Optional">
                            </div>
                            <div>
                                <label class="block mb-2 font-semibold">Feedback Text</label>
                                <textarea id="feedbackText" class="w-full border rounded px-3 py-2" placeholder="Optional"></textarea>
                            </div>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Complete',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        const feedbackRating = document.getElementById('feedbackRating').value;
                        const feedbackText = document.getElementById('feedbackText').value;
                        return fetch(`/management/collection_schedules/${scheduleId}/complete`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                feedback_rating: feedbackRating,
                                feedback_text: feedbackText,
                            }),
                        })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(data => {
                                        throw new Error(data.error || 'Failed to complete collection.');
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                Swal.fire('Success', 'Collection marked as completed.', 'success').then(() => {
                                    window.location.reload();
                                });
                            })
                            .catch(error => {
                                Swal.fire('Error', error.message, 'error');
                            });
                    }
                });
            });
        });

        // Table Action Buttons
        document.querySelectorAll('.schedule-btn').forEach(button => {
            button.addEventListener('click', () => {
                const residentId = button.dataset.residentId;
                scheduleModal.classList.add('show');
                scheduleForSelect.value = 'individual';
                individualResidentDiv.classList.remove('hidden');
                areaDiv.classList.add('hidden');
                document.querySelector('#scheduleForm [name="resident_id"]').value = residentId;
                $('.select2').trigger('change');
            });
        });

        document.querySelectorAll('.edit-schedule-btn').forEach(button => {
            button.addEventListener('click', () => {
                const data = button.dataset;
                editScheduleModal.classList.add('show');
                document.getElementById('editScheduleId').value = data.scheduleId;
                document.getElementById('editResidentName').value = data.residentName || '';
                document.getElementById('editResidentId').value = data.residentId || '';
                document.getElementById('editCollectorId').value = data.collectorId || '';
                document.getElementById('editWasteType').value = data.wasteType || '';
                document.getElementById('editScheduleType').value = data.scheduleType || '';
                document.getElementById('editSpecificDate').value = data.specificDate || '';
                document.getElementById('editRecurringDay').value = data.recurringDay || '';
                document.getElementById('editMonthlyDay').value = data.monthlyDay || '';

                const val = data.scheduleType || '';
                editSpecificDateDiv.classList.toggle('hidden', val !== 'one_time');
                editRecurringDayDiv.classList.toggle('hidden', !['weekly', 'biweekly'].includes(val));
                editMonthlyDayDiv.classList.toggle('hidden', val !== 'monthly');
            });
        });

        // Assign Modal
        const assignModal = document.getElementById('assignModal');
        const openAssignModalBtn = document.getElementById('openAssignModal');
        const closeAssignModalBtn = document.getElementById('closeAssignModal');
        const assignToSelect = document.getElementById('assignTo');
        const assignIndividualResidentDiv = document.getElementById('assignIndividualResidentDiv');
        const assignAreaDiv = document.getElementById('assignAreaDiv');
        const submitAssignBtn = document.getElementById('submitAssign');

        openAssignModalBtn.addEventListener('click', () => {
            assignModal.classList.add('show');
        });

        closeAssignModalBtn.addEventListener('click', () => {
            assignModal.classList.remove('show');
            resetAssignForm();
        });

        assignToSelect.addEventListener('change', () => {
            const val = assignToSelect.value;
            assignIndividualResidentDiv.classList.toggle('hidden', val !== 'individual');
            assignAreaDiv.classList.toggle('hidden', val !== 'area');
        });

        submitAssignBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const form = document.getElementById('assignForm');
            if (assignToSelect.value === 'individual' && !form.querySelector('[name="resident_ids[]"]').value) {
                Swal.fire('Error', 'Please select a resident.', 'error');
            } else if (assignToSelect.value === 'area' && !form.querySelector('[name="area_id"]').value) {
                Swal.fire('Error', 'Please select an area.', 'error');
            } else {
                Swal.fire({
                    title: 'Confirm Assignment',
                    text: 'Are you sure you want to assign this collector?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Assign',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        submitAssignBtn.disabled = true;
                        submitAssignBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" /></svg> Assigning...';
                        form.submit();
                    }
                });
            }
        });

        function resetAssignForm() {
            document.getElementById('assignForm').reset();
            assignIndividualResidentDiv.classList.add('hidden');
            assignAreaDiv.classList.add('hidden');
            $('.select2').val('').trigger('change');
            submitAssignBtn.disabled = false;
            submitAssignBtn.innerHTML = 'Assign';
        }
    });
</script>
@endsection