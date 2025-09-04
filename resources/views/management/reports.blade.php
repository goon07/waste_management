@extends('layouts.app')

@section('content')
<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-6">Reports</h2>

        @if($reports->isEmpty())
            <p>No reports available.</p>
        @else
            <div class="bg-white rounded-xl shadow-md p-6">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-3 text-left">Pickup ID</th>
                            <th class="p-3 text-left">Resident</th>
                            <th class="p-3 text-left">Waste Type</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-left">Rating</th>
                            <th class="p-3 text-left">Feedback</th>
                            <th class="p-3 text-left">Confirmed by Collector</th>
                            <th class="p-3 text-left">Confirmed by Resident</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                            <tr class="border-b">
                                <td class="p-3">{{ $report['pickup_id'] }}</td>
                                <td class="p-3">{{ $report['resident_name'] ?? 'Unknown' }}</td>
                                <td class="p-3">{{ ucfirst($report['waste_type']) ?? 'N/A' }}</td>
                                <td class="p-3">{{ ucfirst($report['status']) }}</td>
                                <td class="p-3">{{ $report['rating'] ?? 'N/A' }}</td>
                                <td class="p-3">{{ $report['feedback'] ?? 'N/A' }}</td>
                                <td class="p-3">{{ $report['confirmed_by_collector'] ? 'Yes' : 'No' }}</td>
                                <td class="p-3">{{ $report['confirmed_by_resident'] ? 'Yes' : 'No' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
@endsection
