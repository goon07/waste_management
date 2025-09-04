@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Issues</h2>

    <!-- Add Issue Button -->
    <button @click="openCreateModal = true" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 mb-4">Add Issue</button>

    <!-- Issues Table -->
    <table class="min-w-full bg-white rounded shadow">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2">Title</th>
                <th class="px-4 py-2">Description</th>
                <th class="px-4 py-2">Assigned To</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($issues as $issue)
            <tr class="border-b">
                <td class="px-4 py-2">{{ $issue->title }}</td>
                <td class="px-4 py-2">{{ $issue->description }}</td>
                <td class="px-4 py-2">{{ $issue->assignedTo?->name ?? 'Unassigned' }}</td>
                <td class="px-4 py-2">{{ $issue->active ? 'Active' : 'Inactive' }}</td>
                <td class="px-4 py-2 space-x-2">
                    <button @click="openEditModal({{ $issue }})" class="text-blue-600 hover:underline">Edit</button>

                    <form action="{{ $issue->active ? route('management.issues.deactivate', $issue->id) : route('management.issues.activate', $issue->id) }}" method="POST" class="inline">
                        @csrf
                        <button class="{{ $issue->active ? 'text-red-600' : 'text-green-600' }} hover:underline">
                            {{ $issue->active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Alpine.js for Modals -->
<div x-data="issueModals()" x-cloak>
    <!-- Create Issue Modal -->
    <div x-show="openCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-bold mb-4">Add Issue</h3>
            <form action="{{ route('management.issues.store') }}" method="POST">
                @csrf
                <input type="text" name="title" placeholder="Title" class="w-full mb-2 border px-3 py-2 rounded" required>
                <textarea name="description" placeholder="Description" class="w-full mb-2 border px-3 py-2 rounded" required></textarea>
                <select name="assigned_to" class="w-full mb-2 border px-3 py-2 rounded">
                    <option value="">Assign to Collector (Optional)</option>
                    @foreach($collectors as $collector)
                        <option value="{{ $collector->id }}">{{ $collector->name }}</option>
                    @endforeach
                </select>
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" @click="openCreateModal=false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Create</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Issue Modal -->
    <div x-show="openEditModalFlag" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-bold mb-4">Edit Issue</h3>
            <form :action="'/management-admin/issues/' + selectedIssue.id" method="POST">
                @csrf
                @method('PUT')
                <input type="text" name="title" x-model="selectedIssue.title" placeholder="Title" class="w-full mb-2 border px-3 py-2 rounded" required>
                <textarea name="description" x-model="selectedIssue.description" placeholder="Description" class="w-full mb-2 border px-3 py-2 rounded" required></textarea>
                <select name="assigned_to" x-model="selectedIssue.assigned_to" class="w-full mb-2 border px-3 py-2 rounded">
                    <option value="">Unassigned</option>
                    @foreach($collectors as $collector)
                        <option :value="{{ $collector->id }}">{{ $collector->name }}</option>
                    @endforeach
                </select>
                <select name="active" x-model="selectedIssue.active" class="w-full mb-2 border px-3 py-2 rounded">
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
function issueModals() {
    return {
        openCreateModal: false,
        openEditModalFlag: false,
        selectedIssue: {},
        openEditModal(issue) {
            this.selectedIssue = {...issue};
            this.openEditModalFlag = true;
        }
    }
}
</script>
@endsection
