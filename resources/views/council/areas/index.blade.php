@extends('layouts.app')

@section('content')
<section class="py-12 bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Areas</h1>
            <a href="{{ route('council.areas.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">+ Add Area</a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded shadow">
                {{ session('error') }}
            </div>
        @endif

        @if($areas->isEmpty())
            <p class="text-gray-600">No areas found.</p>
        @else
            <table class="w-full border-collapse border rounded shadow bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border px-4 py-2 text-left">Name</th>
                        <th class="border px-4 py-2 text-left">Description</th>
                        <th class="border px-4 py-2 text-center w-32">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($areas as $area)
                    <tr>
                        <td class="border px-4 py-2">{{ $area->name }}</td>
                        <td class="border px-4 py-2">{{ $area->description ?? '-' }}</td>
                        <td class="border px-4 py-2 text-center space-x-2">
                            <a href="{{ route('council.areas.edit', $area) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('council.areas.destroy', $area) }}" method="POST" class="inline" onsubmit="return confirm('Delete this area?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</section>
@endsection