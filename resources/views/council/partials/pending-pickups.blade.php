@extends('layouts.app')

@section('content')
    <section class="py-20 bg-gray-100">
<div id="pending-pickups" class="tab-pane" data-intro="Schedule pending pickups here">
    <div class="mt-6 bg-white rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Pending Pickup Requests</h3>
        @if ($pendingPickups->isEmpty())
            <p>No pending pickups.</p>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach ($pendingPickups as $pickup)
                    <li class="py-4 flex justify-between items-center">
                        <div>
                            <p>{{ $pickup->user->name ?? 'Unknown' }} - {{ ucfirst($pickup->waste_type) }}</p>
                            <p class="text-sm text-gray-600">Requested: {{ \Carbon\Carbon::parse($pickup->created_at)->format('Y-m-d H:i') }}</p>
                        </div>
                        <form action="{{ route('council.pickup.schedule', $pickup->id) }}" method="POST">
                            @csrf
                            <div class="flex items-center space-x-4">
                                <select name="collector_id" class="px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Select Collector</option>
                                    @foreach ($collectors as $collector)
                                        <option value="{{ $collector->id }}">{{ $collector->name }}</option>
                                    @endforeach
                                </select>
                                <input type="date" name="scheduled_date" class="px-4 py-2 border border-gray-300 rounded-lg" min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}" required>
                                <button type="submit" class="text-green-600 hover:underline">Schedule</button>
                            </div>
                            @error('collector_id')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                            @error('scheduled_date')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
  </section>
@endsection