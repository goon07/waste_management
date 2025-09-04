@extends('layouts.app')

@section('content')
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Reports</h2>
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Collection Reports</h3>
                @if ($reports->isEmpty())
                    <p>No reports found.</p>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pickup ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resident</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feedback</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($reports as $report)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report['pickup_id'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report['resident_name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report['waste_type'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report['status'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $report['rating'] ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $report['feedback'] ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
                <a href="{{ route('superadmin.dashboard') }}" class="mt-4 inline-block text-green-600 hover:underline">Back to Dashboard</a>
            </div>
        </div>
    </section>
@endsection