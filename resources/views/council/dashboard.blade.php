@extends('layouts.app')

@section('content')
<section class="py-12 bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8 text-gray-800">Council Dashboard Summary</h1>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            <!-- Residents Card -->
            <a href="{{ route('council.users') }}" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                <h2 class="text-xl font-semibold mb-2 text-green-600">Residents</h2>
                <p class="text-4xl font-bold">{{ $residentCount }}</p>
                <p class="mt-2 text-gray-600">Total registered residents</p>
            </a>

            <!-- Issues Card -->
            <a href="{{ route('council.issues') }}" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                <h2 class="text-xl font-semibold mb-2 text-red-600">Issues</h2>
                <ul class="text-gray-700">
                    <li>Reported: {{ $issuesSummary['reported'] ?? 0 }}</li>
                    <li>In Progress: {{ $issuesSummary['in_progress'] ?? 0 }}</li>
                    <li>Resolved: {{ $issuesSummary['resolved'] ?? 0 }}</li>
                </ul>
            </a>

            <!-- Pickups Card -->
            <a href="{{ route('council.pickups') }}" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
                <h2 class="text-xl font-semibold mb-2 text-blue-600">Pickups</h2>
                <p><strong>Last Completed:</strong><br>
                    @if($lastPickup)
                        {{ \Carbon\Carbon::parse($lastPickup->completed_date)->format('Y-m-d') }} ({{ $lastPickup->wasteType->name ?? 'N/A' }})
                    @else
                        None
                    @endif
                </p>
                <p class="mt-4"><strong>Next Scheduled:</strong><br>
                    @if($nextPickup)
                        {{ \Carbon\Carbon::parse($nextPickup->scheduled_date)->format('Y-m-d') }} ({{ $nextPickup->wasteType->name ?? 'N/A' }})
                    @else
                        None
                    @endif
                </p>
            </a>

            <!-- Payments Card -->
<a href="{{ route('council.payments') }}" class="block bg-white rounded-lg shadow p-6 hover:shadow-lg transition">
    <h2 class="text-xl font-semibold mb-2 text-purple-600">Payments</h2>
    <ul class="text-gray-700">
        <li>Registered Residents: {{ $residentCount }}</li>
        <li>Paid: {{ $paidCount }}</li>
        <li>Unpaid: {{ $unpaidCount }}</li>
    </ul>
</a>

        </div>
    </div>
</section>
@endsection