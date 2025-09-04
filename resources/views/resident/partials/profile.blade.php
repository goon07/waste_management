<div id="profile-section" data-intro="Edit your profile details here">
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Profile Details</h3>
        <form action="{{ route('resident.profile.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label for="name" class="block text-gray-700 font-medium mb-2">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                @error('name')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                <input type="email" name="email" id="email" value="{{ $user->email }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100" readonly>
            </div>
            <div>
                <label for="address" class="block text-gray-700 font-medium mb-2">Address</label>
                <input type="text" name="address" id="address" value="{{ old('address', $user->address) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                @error('address')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="phone_number" class="block text-gray-700 font-medium mb-2">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number', $user->phone_number) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                @error('phone_number')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="notifications_enabled" class="block text-gray-700 font-medium mb-2">Notifications Enabled</label>
                <input type="checkbox" name="notifications_enabled" id="notifications_enabled" value="1" {{ old('notifications_enabled', $user->notifications_enabled) ? 'checked' : '' }} class="h-5 w-5 text-green-600 rounded">
                @error('notifications_enabled')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="household_size" class="block text-gray-700 font-medium mb-2">Household Size</label>
                <input type="number" name="household_size" id="household_size" value="{{ old('household_size', $residency->household_size ?? '') }}" min="1" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                @error('household_size')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="waste_collection_frequency" class="block text-gray-700 font-medium mb-2">Waste Collection Frequency</label>
                <select name="waste_collection_frequency" id="waste_collection_frequency" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Select Frequency</option>
                    <option value="weekly" {{ old('waste_collection_frequency', $residency->waste_collection_frequency ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="biweekly" {{ old('waste_collection_frequency', $residency->waste_collection_frequency ?? '') == 'biweekly' ? 'selected' : '' }}>Biweekly</option>
                    <option value="monthly" {{ old('waste_collection_frequency', $residency->waste_collection_frequency ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
                @error('waste_collection_frequency')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="billing_address" class="block text-gray-700 font-medium mb-2">Billing Address</label>
                <input type="text" name="billing_address" id="billing_address" value="{{ old('billing_address', $residency->billing_address ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                @error('billing_address')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="council_id" class="block text-gray-700 font-medium mb-2">Council</label>
                <select name="council_id" id="council_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Select Council</option>
                    @foreach ($councils as $council)
                        <option value="{{ $council->id }}" {{ old('council_id', $residency->council_id ?? '') == $council->id ? 'selected' : '' }}>{{ $council->name }}</option>
                    @endforeach
                </select>
                @error('council_id')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="collector_company_id" class="block text-gray-700 font-medium mb-2">Collector Company</label>
                <select name="collector_company_id" id="collector_company_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Select Company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ old('collector_company_id', $residency->collector_company_id ?? '') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
                @error('collector_company_id')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold transition duration-200">Update Profile</button>
        </form>
    </div>
</div>