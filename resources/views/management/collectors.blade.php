@extends('layouts.app')

@section('content')
<div x-data="collectorModals()" x-cloak class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Collectors</h2>

    <!-- Buttons -->
    <div class="flex space-x-4 mb-4">
        <!-- Add Collector Button -->
        <button @click="openCreateModal = true" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Add Collector
        </button>

        <!-- Assign Button -->
        <button @click="openAssignModal = true" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Assign
        </button>
    </div>





    

    <!-- Collectors Table -->
    <h3 class="text-xl font-semibold mb-2">Collectors</h3>
    <table class="min-w-full bg-white rounded shadow mb-8">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Phone</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($collectors as $collector)
            <tr class="border-b">
                <td class="px-4 py-2">{{ $collector->name }}</td>
                <td class="px-4 py-2">{{ $collector->email }}</td>
                <td class="px-4 py-2">{{ $collector->phone_number ?? 'N/A' }}</td>
                <td class="px-4 py-2">{{ $collector->active ? 'Active' : 'Inactive' }}</td>
                <td class="px-4 py-2 space-x-2">
                    <button 
                        @click="openEditModal(@json($collector))" 
                        class="text-blue-600 hover:underline">Edit</button>

                    <form action="{{ $collector->active ? route('management.collectors.deactivate', $collector->id) : route('management.collectors.activate', $collector->id) }}" method="POST" class="inline">
                        @csrf
                        <button class="{{ $collector->active ? 'text-red-600' : 'text-green-600' }} hover:underline">
                            {{ $collector->active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

 

  

    <!-- Create Modal -->
    <div x-show="openCreateModal" 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" 
         style="display: none;"
         @keydown.escape.window="openCreateModal = false">
        <div class="bg-white rounded-lg p-6 w-full max-w-md" @click.away="openCreateModal = false">
            <h3 class="text-xl font-bold mb-4">Add Collector</h3>
            <form action="{{ route('management.collectors.store') }}" method="POST">
                @csrf
                <input type="text" name="name" placeholder="Name" class="w-full mb-2 border px-3 py-2 rounded" required>
                <input type="email" name="email" placeholder="Email" class="w-full mb-2 border px-3 py-2 rounded" required>
                <input type="password" name="password" placeholder="Password" class="w-full mb-2 border px-3 py-2 rounded" required>
                <input type="text" name="phone_number" placeholder="Phone" class="w-full mb-2 border px-3 py-2 rounded">
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" @click="openCreateModal=false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="openEditModalFlag" 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50" 
         style="display: none;"
         @keydown.escape.window="openEditModalFlag = false">
        <div class="bg-white rounded-lg p-6 w-full max-w-md" @click.away="openEditModalFlag = false">
            <h3 class="text-xl font-bold mb-4">Edit Collector</h3>
            <form :action="'/management-admin/collectors/' + selectedCollector.id" method="POST">
                @csrf
                @method('PUT')
                <input type="text" name="name" x-model="selectedCollector.name" placeholder="Name" class="w-full mb-2 border px-3 py-2 rounded" required>
                <input type="text" name="phone_number" x-model="selectedCollector.phone_number" placeholder="Phone" class="w-full mb-2 border px-3 py-2 rounded">
                <select name="active" x-model="selectedCollector.active" class="w-full mb-2 border px-3 py-2 rounded">
                    <option :value="1">Active</option>
                    <option :value="0">Inactive</option>
                </select>
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" @click="openEditModalFlag=false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Modal -->
    <div x-show="openAssignModal" 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 overflow-auto p-4" 
         style="display: none;"
         @keydown.escape.window="openAssignModal = false">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg max-h-full overflow-y-auto" @click.away="openAssignModal = false">
            <h3 class="text-xl font-bold mb-4">Assign Residents to Collector</h3>
            <form action="{{ route('management.collectors.assign') }}" method="POST">
                @csrf

                <!-- Select Collector -->
                <label for="collector_id" class="block font-semibold mb-1">Select Collector</label>
                <select name="collector_id" id="collector_id" required class="w-full mb-4 border px-3 py-2 rounded">
                    <option value="" disabled selected>-- Select Collector --</option>
                    @foreach($collectors as $collector)
                        <option value="{{ $collector->id }}">{{ $collector->name }} ({{ $collector->email }})</option>
                    @endforeach
                </select>

                <!-- Assignment Type -->
                <label class="block font-semibold mb-1">Assignment Type</label>
                <div class="mb-4 space-y-2">
                    <label class="inline-flex items-center">
                        <input type="radio" name="assignment_type" value="area" x-model="assignmentType" required>
                        <span class="ml-2">Assign by Area</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="assignment_type" value="individual" x-model="assignmentType" required>
                        <span class="ml-2">Assign Individual Residents</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="assignment_type" value="bulk" x-model="assignmentType" required>
                        <span class="ml-2">Assign All Residents (Bulk)</span>
                    </label>
                </div>

                <!-- Area Selection (shown if assignmentType == 'area') -->
                <div x-show="assignmentType === 'area'" class="mb-4">
                    <label for="area" class="block font-semibold mb-1">Select Area</label>
                    <select name="area" id="area" class="w-full border px-3 py-2 rounded">
                        <option value="" disabled selected>-- Select Area --</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->name }}">{{ $area->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Assign all residents in the selected area.</p>
                </div>

                <!-- Individual Residents Selection (shown if assignmentType == 'individual') -->
                <div x-show="assignmentType === 'individual'" class="mb-4">
                    <label for="resident_ids" class="block font-semibold mb-1">Select Residents</label>
                    <select name="resident_ids[]" id="resident_ids" multiple size="8" class="w-full border px-3 py-2 rounded">
                        @foreach($residents as $resident)
                            <option value="{{ $resident->id }}">
                                {{ $resident->name }} - {{ optional($resident->residency)->area->name ?? 'No Area' }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Hold Ctrl (Cmd on Mac) to select multiple residents.</p>
                </div>

                <!-- Bulk assignment info (shown if assignmentType == 'bulk') -->
                <div x-show="assignmentType === 'bulk'" class="mb-4">
                    <p>All residents assigned to your company will be assigned to the selected collector.</p>
                </div>

                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" @click="openAssignModal=false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function collectorModals() {
    return {
        openCreateModal: false,
        openEditModalFlag: false,
        openAssignModal: false,
        assignmentType: null,
        selectedCollector: {},
        openEditModal(collector) {
            this.selectedCollector = collector;
            this.openEditModalFlag = true;
        }
    }
}
</script>
@endsection