@extends('layouts.app')

@section('content')
<section class="py-12 bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 max-w-3xl">
        <h1 class="text-3xl font-bold mb-6">Create New User</h1>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">{{ session('error') }}</div>
        @endif

        <form action="{{ route('council.user.create') }}" method="POST" class="bg-white p-6 rounded shadow">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                    @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="block font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                    @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="role" class="block font-medium text-gray-700">Role</label>
                    <select name="role" id="role" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                        <option value="resident" {{ old('role') == 'resident' ? 'selected' : '' }}>Resident</option>
                        <option value="collector" {{ old('role') == 'collector' ? 'selected' : '' }}>Collector</option>
                    </select>
                    @error('role') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="collector_company_id" class="block font-medium text-gray-700">Collector Company (Collectors only)</label>
                    <select name="collector_company_id" id="collector_company_id" class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">None</option>
                        @foreach ($collectorCompanies as $company)
                            <option value="{{ $company->id }}" {{ old('collector_company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    @error('collector_company_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="block font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                    @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block font-medium text-gray-700">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 w-full border border-gray-300 rounded px-3 py-2">
                </div>
            </div>

            <button type="submit" class="mt-6 bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">Create User</button>
        </form>
    </div>
</section>
@endsection