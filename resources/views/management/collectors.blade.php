@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Collectors</h2>

    <!-- Add Collector Button -->
    <button @click="openCreateModal = true" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 mb-4">Add Collector</button>

    <!-- Collectors Table -->
    <table class="min-w-full bg-white rounded shadow">
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
                        @click="openEditModal({{ $collector }})" 
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
</div>

<!-- Alpine.js for modals -->
<div x-data="collectorModals()" x-cloak>
    <!-- Create Modal -->
    <div x-show="openCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-bold mb-4">Add Collector</h3>
            <form action="{{ route('management.collectors.store') }}" method="POST">
                @csrf
                <input type="text" name="name" placeholder="Name" class="w-full mb-2 border px-3 py-2 rounded" required>
                <input type="email" name="email" placeholder="Email" class="w-full mb-2 border px-3 py-2 rounded" required>
                <input type="text" name="phone_number" placeholder="Phone" class="w-full mb-2 border px-3 py-2 rounded">
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" @click="openCreateModal=false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="openEditModalFlag" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
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
</div>

<script>
function collectorModals() {
    return {
        openCreateModal: false,
        openEditModalFlag: false,
        selectedCollector: {},
        openEditModal(collector) {
            this.selectedCollector = {...collector};
            this.openEditModalFlag = true;
        }
    }
}
</script>
@endsection
