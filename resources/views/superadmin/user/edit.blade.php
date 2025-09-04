@extends('layouts.app')

@section('content')
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Edit User</h2>
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
                <form action="{{ route('superadmin.user.edit', $user->id) }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        @error('email')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-medium mb-2">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 font-medium mb-2">Password (Optional)</label>
                        <input type="password" name="password" id="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @error('password')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label for="role" class="block text-gray-700 font-medium mb-2">Role</label>
                        <select name="role" id="role" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                            <option value="superadmin" {{ old('role', $user->role) == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="council_admin" {{ old('role', $user->role) == 'council_admin' ? 'selected' : '' }}>Council Admin</option>
                            <option value="collection_admin" {{ old('role', $user->role) == 'collection_admin' ? 'selected' : '' }}>Collection Admin</option>
                            <option value="collector" {{ old('role', $user->role) == 'collector' ? 'selected' : '' }}>Collector</option>
                            <option value="resident" {{ old('role', $user->role) == 'resident' ? 'selected' : '' }}>Resident</option>
                        </select>
                        @error('role')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 font-medium mb-2">Address (Optional)</label>
                        <input type="text" name="address" id="address" value="{{ old('address', $user->address) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @error('address')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="phone_number" class="block text-gray-700 font-medium mb-2">Phone Number (Optional)</label>
                        <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $user->phone_number) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @error('phone_number')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="council_id" class="block text-gray-700 font-medium mb-2">Council (Optional)</label>
                        <select name="council_id" id="council_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                            <option value="">Select Council</option>
                            @foreach ($councils as $council)
                                <option value="{{ $council->id }}" {{ old('council_id', $user->council_id) == $council->id ? 'selected' : '' }}>{{ $council->name }}</option>
                            @endforeach
                        </select>
                        @error('council_id')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="collector_company_id" class="block text-gray-700 font-medium mb-2">Collection Company (Optional)</label>
                        <select name="collector_company_id" id="collector_company_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                            <option value="">Select Company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" {{ old('collector_company_id', $user->collector_company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                        @error('collector_company_id')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold">Update User</button>
                    <a href="{{ route('superadmin.dashboard') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
                </form>
            </div>
        </div>
    </section>
@endsection