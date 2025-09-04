@extends('layouts.app')

@section('content')
    <section class="py-20 bg-gray-100">
<div id="scheduled-pickups" class="tab-pane">
    <div class="mt-6 bg-white rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Scheduled Pickups</h3>
        @if ($scheduledPickups->isEmpty())
            <p>No scheduled pickups.</p>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($scheduledPickups as $pickup)
                    <li class="py-4">
                        <p>{{ $pickup->user->name ?? 'Unknown' }} - {{ ucfirst($pickup->waste_type) }}</p>
                        <p class="text-sm text-gray-600">Scheduled: {{ $pickup->scheduled_date ? \Carbon\Carbon::parse($pickup->scheduled_date)->format('Y-m-d H:i') : 'N/A' }}</p>
                        <p class="text-sm text-gray-600">Collector ID: {{ $pickup->collector_id ?? 'N/A' }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
  </section>
@endsection