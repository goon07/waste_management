@extends('layouts.app')

@section('content')
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Edit Collection Company</h2>
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
                <form action="{{ route('superadmin.company.edit', $company->id) }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-medium mb-2">Company Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="contact_email" class="block text-gray-700 font-medium mb-2">Contact Email (Optional)</label>
                        <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $company->contact_email) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @error('contact_email')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="phone_number" class="block text-gray-700 font-medium mb-2">Phone Number (Optional)</label>
                        <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $company->phone_number) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @error('phone_number')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 font-medium mb-2">Address (Optional)</label>
                        <input type="text" name="address" id="address" value="{{ old('address', $company->address) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @error('address')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="license_number" class="block text-gray-700 font-medium mb-2">License Number (Optional)</label>
                        <input type="text" name="license_number" id="license_number" value="{{ old('license_number', $company->license_number) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @error('license_number')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="status" class="block text-gray-700 font-medium mb-2">Status</label>
                        <select name="status" id="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                            <option value="active" {{ old('status', $company->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $company->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold">Update Company</button>
                    <a href="{{ route('superadmin.dashboard') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
                </form>
            </div>
        </div>
    </section>
@endsection