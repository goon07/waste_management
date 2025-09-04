@extends('layouts.app')

@section('content')
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Edit Residency</h2>
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
                <form action="{{ route('superadmin.residency.edit', $residency->id) }}" method="POST">
                    @csrf
                    @method('POST')
                    <div class="mb-4">
                        <label for="user_id" class="block text-gray-700 font-medium mb-2">Resident</label>
                        <select name="user_id" id="user_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                            <option value="">Select Resident</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $residency->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="council_id" class="block text-gray-700 font-medium mb-2">Council</label>
                        <select name="council_id" id="council_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                            <option value="">Select Council</option>
                            @foreach ($councils as $council)
                                <option value="{{ $council->id }}" {{ old('council_id', $residency->council_id) == $council->id ? 'selected' : '' }}>{{ $council->name }}</option>
                            @endforeach
                        </select>
                        @error('council_id')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="collector_company_id" class="block text-gray-700 font-medium mb-2">Collection Company</label>
                        <select name="collector_company_id" id="collector_company_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                            <option value="">Select Company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" {{ old('collector_company_id', $residency->collector_company_id) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                        @error('collector_company_id')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="household_size" class="block text-gray-700 font-medium mb-2">Household Size (Optional)</label>
                        <input type="number" name="household_size" id="household_size" value="{{ old('household_size', $residency->household_size) }}" min="1" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @error('household_size')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="waste_collection_frequency" class="block text-gray-700 font-medium mb-2">Waste Collection Frequency (Optional)</label>
                        <select name="waste_collection_frequency" id="waste_collection_frequency" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                            <option value="">Select Frequency</option>
                            <option value="weekly" {{ old('waste_collection_frequency', $residency->waste_collection_frequency) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="biweekly" {{ old('waste_collection_frequency', $residency->waste_collection_frequency) == 'biweekly' ? 'selected' : '' }}>Biweekly</option>
                            <option value="monthly" {{ old('waste_collection_frequency', $residency->waste_collection_frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                        @error('waste_collection_frequency')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="billing_address" class="block text-gray-700 font-medium mb-2">Billing Address (Optional)</label>
                        <input type="text" name="billing_address" id="billing_address" value="{{ old('billing_address', $residency->billing_address) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @error('billing_address')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold">Update Residency</button>
                    <a href="{{ route('superadmin.dashboard') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
                </form>
            </div>
        </div>
    </section>
@endsection