@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Add Collector</h1>

    <form method="POST" action="{{ route('council.collectors.store') }}">
        @csrf

        <div class="mb-4">
            <label class="block font-medium mb-1" for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" />
            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block font-medium mb-1" for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                class="w-full border rounded px-3 py-2 @error('email') border-red-500 @enderror" />
            @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        @if(!$council->employs_collectors)
        <div class="mb-4">
            <label class="block font-medium mb-1" for="collector_company_id">Collector Company</label>
            <select id="collector_company_id" name="collector_company_id" required
                class="w-full border rounded px-3 py-2 @error('collector_company_id') border-red-500 @enderror">
                <option value="">Select a company</option>
                @foreach($collectorCompanies as $company)
                    <option value="{{ $company->id }}" @selected(old('collector_company_id') == $company->id)>{{ $company->name }}</option>
                @endforeach
            </select>
            @error('collector_company_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>
        @endif

        <div class="mb-4">
            <label class="block font-medium mb-1" for="password">Password</label>
            <input id="password" name="password" type="password" required
                class="w-full border rounded px-3 py-2 @error('password') border-red-500 @enderror" />
            @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block font-medium mb-1" for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required
                class="w-full border rounded px-3 py-2" />
        </div>

        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Create Collector</button>
    </form>
</div>
@endsection