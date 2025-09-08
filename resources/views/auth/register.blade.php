@extends('layouts.app')

@section('content')
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4 max-w-md">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Join Waste Management</h2>
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="bg-white rounded-xl shadow-md p-6">
                <form action="{{ route('custom.register') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-medium mb-2">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        @error('email')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                        <input type="password" name="password" id="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        @error('password')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                    </div>
                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 font-medium mb-2">Address</label>
                        <input type="text" name="address" id="address" value="{{ old('address') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                        @error('address')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="phone_number" class="block text-gray-700 font-medium mb-2">Phone Number (Optional)</label>
                        <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        @error('phone_number')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="council_id" class="block text-gray-700 font-medium mb-2">Select Council</label>
                        <select name="council_id" id="council_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                            <option value="">Select Council</option>
                            @foreach ($councils as $council)
                                <option value="{{ $council->id }}" {{ old('council_id') == $council->id ? 'selected' : '' }}>{{ $council->name }}</option>
                            @endforeach
                        </select>
                        @error('council_id')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold w-full">Register</button>
                </form>
            </div>
        </div>
    </section>
@endsection