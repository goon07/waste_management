@extends('layouts.app')

@section('content')
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Edit Council</h2>
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
                <form action="{{ route('superadmin.council.edit', $council->id) }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-medium mb-2">Council Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $council->name) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="region" class="block text-gray-700 font-medium mb-2">Region</label>
                        <input type="text" name="region" id="region" value="{{ old('region', $council->region) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        @error('region')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold">Update Council</button>
                    <a href="{{ route('superadmin.dashboard') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
                </form>
            </div>
        </div>
    </section>
@endsection