@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Edit Collector Company</h1>

    <form method="POST" action="{{ route('council.companies.update', $company->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block font-medium mb-1" for="name">Company Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $company->name) }}" required
                class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" />
            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Update Company</button>
    </form>
</div>
@endsection