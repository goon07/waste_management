@extends('layouts.app')

@section('content')
<section class="py-12 bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 max-w-3xl">
        <h1 class="text-3xl font-bold mb-6">Add New Area</h1>

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded shadow">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('council.areas.store') }}" method="POST" class="bg-white p-6 rounded shadow">
            @csrf
            <div class="mb-4">
                <label for="name" class="block font-medium text-gray-700">Area Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block font-medium text-gray-700">Description (optional)</label>
                <textarea name="description" id="description" rows="3" class="mt-1 w-full border border-gray-300 rounded px-3 py-2">{{ old('description') }}</textarea>
                @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Create Area</button>
            <a href="{{ route('council.areas.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
        </form>
    </div>
</section>
@endsection