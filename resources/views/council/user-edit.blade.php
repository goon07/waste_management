@extends('layouts.app')

@section('content')
<section class="py-12 bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 max-w-3xl">
        <h1 class="text-3xl font-bold mb-6">Edit User: {{ $user->name }}</h1>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">{{ session('error') }}</div>
        @endif

        <form action="{{ route('council.user.update', $user->id) }}" method="POST" class="bg-white p-6 rounded shadow">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                    @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="block font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                    @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="role" class="block font-medium text-gray-700">Role</label>
                    <select name="role" id="role" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                        <option value="resident" {{ old('role', $user->role) == 'resident' ? 'selected' : '' }}>Resident</option>
                        <option value="collector" {{ old('role', $user->role) == 'collector' ? 'selected' : '' }}>Collector</option>
                    </select>
                    @error('role') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="collector_company_id" class="block font-medium text-gray-700">Collector Company (Collectors only)</label>
                    <select name="collector_company_id" id="collector_company_id" class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">None</option>
                        @foreach ($collectorCompanies as $company)
                            <option value="{{ $company->id }}" {{ old('collector_company_id', $user->collector_company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('collector_company_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="address" class="block font-medium text-gray-700">Address</label>
                    <input type="text" name="address" id="address" value="{{ old('address', $user->address) }}" class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                    @error('address') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
    <label for="area_id" class="block font-medium text-gray-700">Area</label>
    <select name="area_id" id="area_id" class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
        <option value="">None</option>
        @foreach ($areas as $area)
            <option value="{{ $area->id }}" {{ old('area_id', $user->residency->area_id ?? '') == $area->id ? 'selected' : '' }}>
                {{ $area->name }}
            </option>
        @endforeach
    </select>
    @error('area_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
</div>

            <button type="submit" class="mt-6 bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update User</button>
        </form>
    </div>
</section>
@endsection