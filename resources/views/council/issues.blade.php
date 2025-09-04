@extends('layouts.app')

@section('content')
<section class="py-20 bg-gray-100">
    <div id="issues" class="tab-pane" data-intro="Manage reported issues here">
        <div class="mt-6 bg-white rounded-xl shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Reported Issues</h3>

                <!-- Filter -->
                <form method="GET" action="{{ route('council.issues') }}" class="flex items-center space-x-2">
                    <label for="status" class="text-sm text-gray-600">Filter:</label>
                    <select name="status" id="status" 
                            onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">All</option>
                        <option value="reported" {{ request('status') == 'reported' ? 'selected' : '' }}>Reported</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </form>
            </div>

            @if ($issues->isEmpty())
                <p class="text-gray-500">No issues reported.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700 uppercase text-sm">
                                <th class="px-4 py-3">Reporter</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($issues as $issue)
                                <tr>
                                    <td class="px-4 py-3">{{ $issue->user->name ?? 'Unknown' }}</td>
                                    <td class="px-4 py-3">{{ ucfirst($issue->issue_type) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $issue->description }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColors = [
                                                'reported' => 'bg-red-100 text-red-700',
                                                'in_progress' => 'bg-yellow-100 text-yellow-700',
                                                'resolved' => 'bg-green-100 text-green-700',
                                            ];
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$issue->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <form action="{{ route('council.issue.status', $issue->id) }}" method="POST" class="flex items-center justify-end space-x-2">
                                            @csrf
                                            <select name="status" class="px-3 py-1 border border-gray-300 rounded-lg text-sm">
                                                <option value="reported" {{ $issue->status == 'reported' ? 'selected' : '' }}>Reported</option>
                                                <option value="in_progress" {{ $issue->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                <option value="resolved" {{ $issue->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                            </select>
                                            <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded-lg text-sm hover:bg-green-600">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
