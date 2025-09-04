@extends('layouts.app')

@section('content')
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Waste Guide</h2>
            @if ($wasteGuide->isEmpty())
                <p>No waste guide entries found.</p>
            @else
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Waste Guide</h3>
                    <ul class="divide-y divide-gray-200">
                        @foreach ($wasteGuide as $item)
                            <li class="py-4">
                                <p>{{ ucfirst($item->item_name) }}</p>
                                <p class="text-sm text-gray-600">Category: {{ ucfirst($item->category) }}</p>
                                <p class="text-sm text-gray-600">Description: {{ $item->description }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>
@endsection