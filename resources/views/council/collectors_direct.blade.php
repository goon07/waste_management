@extends('layouts.app')

@section('content')
<section class="py-12 bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 max-w-7xl">
        <h1 class="text-4xl font-extrabold mb-8 text-gray-900">Collectors Employed by Council</h1>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow" role="alert">
                {{ session('success') }}
            </div>
        @endif

        {{-- Add Collector Button --}}
        <a href="{{ route('council.collectors.create') }}" 
           class="mb-6 inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
           + Add Collector
        </a>

        {{-- Collectors Table --}}
        <div class="bg-white rounded-lg shadow p-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($collectors as $collector)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $collector->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $collector->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                @if($collector->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm space-x-2">
                                <a href="{{ route('council.collectors.edit', $collector->id) }}" class="text-indigo-600 hover:underline">Edit</a>

                                <form action="{{ route('council.collectors.deactivate', $collector->id) }}" method="POST" class="inline" 
                                    onsubmit="return confirm('Change status for this collector?')">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:underline">
                                        {{ $collector->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No collectors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection