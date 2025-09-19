@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6">Create Collection</h1>
    <div class="bg-white rounded-xl shadow-md p-6">
        <form action="{{ route('management.collections.schedule') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block mb-2 font-semibold">Resident</label>
                    @if($resident)
                        <input type="hidden" name="resident_id" value="{{ $resident->id }}">
                        <input type="text" value="{{ $resident->name }}" class="w-full border rounded px-3 py-2" readonly>
                        <input type="hidden" name="schedule_for" value="individual">
                    @else
                        <select name="resident_id" class="w-full border rounded px-3 py-2 select2">
                            <option value="">Select Resident</option>
                            @foreach($residents as $resident)
                                <option value="{{ $resident->id }}">{{ $resident->name }} ({{ optional($resident->residency->area)->name ?? 'No Area' }})</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="schedule_for" value="individual">
                    @endif
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
                    <select name="waste_type" class="w-full border rounded px-3 py-2" required>
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
                        <option value="specific_date">Specific Date</option>
                        <option value="weekly">Weekly</option>
                        <option value="biweekly">Bi-Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <div id="specificDateDiv" class="hidden">
                    <label class="block mb-2 font-semibold">Select Date</label>
                    <input type="date" name="specific_date" min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" max="{{ \Carbon\Carbon::today()->addYear()->format('Y-m-d') }}" class="w-full border rounded px-3 py-2">
                </div>
                <div id="weeklyDayDiv" class="hidden">
                    <label class="block mb-2 font-semibold">Select Day of Week</label>
                    <select name="weekly_day" class="w-full border rounded px-3 py-2">
                        @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                            <option value="{{ $day }}">{{ $day }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="biweeklyDayDiv" class="hidden">
                    <label class="block mb-2 font-semibold">Select Day of Bi-Weekly Schedule</label>
                    <select name="biweekly_day" class="w-full border rounded px-3 py-2">
                        @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                            <option value="{{ $day }}">{{ $day }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="monthlyDayDiv" class="hidden">
                    <label class="block mb-2 font-semibold">Select Day of Month (1-31)</label>
                    <input type="number" name="monthly_day" min="1" max="31" class="w-full border rounded px-3 py-2" placeholder="Day of month">
                </div>
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('management.dashboard') }}" class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">Cancel</a>
                    <button type="submit" id="submitSchedule" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Schedule</button>
                </div>
            </div>
        </form>
    </div>
</div>

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

        // Schedule Type Toggle
        const scheduleTypeSelect = document.getElementById('scheduleType');
        const specificDateDiv = document.getElementById('specificDateDiv');
        const weeklyDayDiv = document.getElementById('weeklyDayDiv');
        const biweeklyDayDiv = document.getElementById('biweeklyDayDiv');
        const monthlyDayDiv = document.getElementById('monthlyDayDiv');
        const submitScheduleBtn = document.getElementById('submitSchedule');

        scheduleTypeSelect.addEventListener('change', () => {
            const val = scheduleTypeSelect.value;
            specificDateDiv.classList.toggle('hidden', val !== 'specific_date');
            weeklyDayDiv.classList.toggle('hidden', val !== 'weekly');
            biweeklyDayDiv.classList.toggle('hidden', val !== 'biweekly');
            monthlyDayDiv.classList.toggle('hidden', val !== 'monthly');
        });

        submitScheduleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const form = document.getElementsByTagName('form')[0];
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
        });
    });
</script>
@endsection