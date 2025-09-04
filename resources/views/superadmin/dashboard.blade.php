@extends('layouts.app')

@section('content')
    <section class="py-12 bg-gray-50 min-h-screen">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-4xl font-bold text-gray-900">Super Admin Dashboard</h2>
                <a href="{{ route('logout') }}" class="text-red-600 hover:text-red-800 font-medium">Logout</a>
            </div>
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            <div class="tabs">
                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex space-x-4">
                        <button class="tab-button border-b-2 border-green-600 text-green-600 px-4 py-2 text-sm font-semibold" data-tab="overview">Overview</button>
                        <button class="tab-button border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 px-4 py-2 text-sm font-semibold" data-tab="councils">Councils</button>
                        <button class="tab-button border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 px-4 py-2 text-sm font-semibold" data-tab="companies">Companies</button>
                        <button class="tab-button border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 px-4 py-2 text-sm font-semibold" data-tab="users">Users</button>
                        <button class="tab-button border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 px-4 py-2 text-sm font-semibold" data-tab="residencies">Residencies</button>
                        <button class="tab-button border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 px-4 py-2 text-sm font-semibold" data-tab="council-companies">Council-Company Assignments</button>
                        <button class="tab-button border-b-2 border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300 px-4 py-2 text-sm font-semibold" data-tab="reports">Reports</button>
                    </nav>
                </div>
                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div id="overview" class="tab-pane">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white rounded-xl shadow-md p-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Total Councils</h3>
                                <p class="text-3xl font-semibold text-green-600">{{ $councils->count() }}</p>
                            </div>
                            <div class="bg-white rounded-xl shadow-md p-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Total Companies</h3>
                                <p class="text-3xl font-semibold text-green-600">{{ $companies->count() }}</p>
                            </div>
                            <div class="bg-white rounded-xl shadow-md p-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Total Users</h3>
                                <p class="text-3xl font-semibold text-green-600">{{ $users->count() }}</p>
                            </div>
                            <div class="bg-white rounded-xl shadow-md p-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Total Residencies</h3>
                                <p class="text-3xl font-semibold text-green-600">{{ $residencies->count() }}</p>
                            </div>
                        </div>
                    </div>
                    <!-- Councils Tab -->
                    <div id="councils" class="tab-pane hidden">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Create Council</h3>
                            <form action="{{ route('superadmin.council.create') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="name" class="block text-gray-700 font-medium mb-2">Council Name</label>
                                    <input type="text" name="name" id="name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="region" class="block text-gray-700 font-medium mb-2">Region</label>
                                    <input type="text" name="region" id="region" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                    @error('region')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="contact_email" class="block text-gray-700 font-medium mb-2">Contact Email (Optional)</label>
                                    <input type="email" name="contact_email" id="contact_email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @error('contact_email')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="phone_number" class="block text-gray-700 font-medium mb-2">Phone Number (Optional)</label>
                                    <input type="text" name="phone_number" id="phone_number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @error('phone_number')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="address" class="block text-gray-700 font-medium mb-2">Address (Optional)</label>
                                    <input type="text" name="address" id="address" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @error('address')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="status" class="block text-gray-700 font-medium mb-2">Status</label>
                                    <select name="status" id="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    @error('status')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold transition duration-200">Create Council</button>
                            </form>
                            <h3 class="text-xl font-bold text-gray-800 mt-6 mb-4">Existing Councils</h3>
                            @if ($councils->isEmpty())
                                <p class="text-gray-600">No councils found.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Region</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($councils as $council)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $council->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $council->region }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($council->status) }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <a href="{{ route('superadmin.council.edit', $council->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Companies Tab -->
                    <div id="companies" class="tab-pane hidden">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Create Collection Company</h3>
                            <form action="{{ route('superadmin.company.create') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="name" class="block text-gray-700 font-medium mb-2">Company Name</label>
                                    <input type="text" name="name" id="name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="contact_email" class="block text-gray-700 font-medium mb-2">Contact Email (Optional)</label>
                                    <input type="email" name="contact_email" id="contact_email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @error('contact_email')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="phone_number" class="block text-gray-700 font-medium mb-2">Phone Number (Optional)</label>
                                    <input type="text" name="phone_number" id="phone_number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @error('phone_number')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="address" class="block text-gray-700 font-medium mb-2">Address (Optional)</label>
                                    <input type="text" name="address" id="address" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @error('address')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="license_number" class="block text-gray-700 font-medium mb-2">License Number (Optional)</label>
                                    <input type="text" name="license_number" id="license_number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @error('license_number')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="status" class="block text-gray-700 font-medium mb-2">Status</label>
                                    <select name="status" id="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    @error('status')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold transition duration-200">Create Company</button>
                            </form>
                            <h3 class="text-xl font-bold text-gray-800 mt-6 mb-4">Existing Companies</h3>
                            @if ($companies->isEmpty())
                                <p class="text-gray-600">No companies found.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($companies as $company)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $company->name }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($company->status) }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <a href="{{ route('superadmin.company.edit', $company->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Users Tab -->
                    <div id="users" class="tab-pane hidden">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Create User</h3>
                            <form action="{{ route('superadmin.user.create') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                                    <input type="email" name="email" id="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                    @error('email')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="name" class="block text-gray-700 font-medium mb-2">Name</label>
                                    <input type="text" name="name" id="name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                                    <input type="password" name="password" id="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                    @error('password')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                </div>
                                <div>
                                    <label for="role" class="block text-gray-700 font-medium mb-2">Role</label>
                                    <select name="role" id="role" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                        <option value="superadmin">Super Admin</option>
                                        <option value="council_admin">Council Admin</option>
                                        <option value="company_admin">Company Admin</option>
                                        <option value="collector">Collector</option>
                                        <option value="resident">Resident</option>
                                    </select>
                                    @error('role')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="address" class="block text-gray-700 font-medium mb-2">Address (Optional)</label>
                                    <input type="text" name="address" id="address" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @error('address')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="phone_number" class="block text-gray-700 font-medium mb-2">Phone Number (Optional)</label>
                                    <input type="text" name="phone_number" id="phone_number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @error('phone_number')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="notifications_enabled" class="block text-gray-700 font-medium mb-2">Notifications Enabled</label>
                                    <input type="checkbox" name="notifications_enabled" id="notifications_enabled" value="1" checked class="h-5 w-5 text-green-600 rounded">
                                    @error('notifications_enabled')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="council_id" class="block text-gray-700 font-medium mb-2">Council (Optional)</label>
                                    <select name="council_id" id="council_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                        <option value="">Select Council</option>
                                        @foreach ($councils as $council)
                                            <option value="{{ $council->id }}">{{ $council->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('council_id')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="collector_company_id" class="block text-gray-700 font-medium mb-2">Collection Company (Optional)</label>
                                    <select name="collector_company_id" id="collector_company_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                        <option value="">Select Company</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('collector_company_id')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold transition duration-200">Create User</button>
                            </form>
                            <h3 class="text-xl font-bold text-gray-800 mt-6 mb-4">Existing Users</h3>
                            @if ($users->isEmpty())
                                <p class="text-gray-600">No users found.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Council</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($users as $user)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->name ?? 'Unknown' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->council ? $user->council->name : 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $user->collectorCompany ? $user->collectorCompany->name : 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <a href="{{ route('superadmin.user.edit', $user->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                                        <form action="{{ route('superadmin.reset-password', $user->email) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="text-green-600 hover:underline ml-4">Reset Password</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Residencies Tab -->
                    <div id="residencies" class="tab-pane hidden">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Create Residency</h3>
                            <form action="{{ route('superadmin.residency.create') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="user_id" class="block text-gray-700 font-medium mb-2">Resident</label>
                                    <select name="user_id" id="user_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                        <option value="">Select Resident</option>
                                        @foreach ($residents as $resident)
                                            <option value="{{ $resident->id }}">{{ $resident->name }} ({{ $resident->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="council_id" class="block text-gray-700 font-medium mb-2">Council</label>
                                    <select name="council_id" id="council_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                        <option value="">Select Council</option>
                                        @foreach ($councils as $council)
                                            <option value="{{ $council->id }}">{{ $council->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('council_id')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="collector_company_id" class="block text-gray-700 font-medium mb-2">Collection Company</label>
                                    <select name="collector_company_id" id="collector_company_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                        <option value="">Select Company</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('collector_company_id')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="household_size" class="block text-gray-700 font-medium mb-2">Household Size (Optional)</label>
                                    <input type="number" name="household_size" id="household_size" min="1" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @error('household_size')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="waste_collection_frequency" class="block text-gray-700 font-medium mb-2">Waste Collection Frequency (Optional)</label>
                                    <select name="waste_collection_frequency" id="waste_collection_frequency" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                        <option value="">Select Frequency</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="biweekly">Biweekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                    @error('waste_collection_frequency')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="billing_address" class="block text-gray-700 font-medium mb-2">Billing Address (Optional)</label>
                                    <input type="text" name="billing_address" id="billing_address" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    @error('billing_address')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold transition duration-200">Create Residency</button>
                            </form>
                            <h3 class="text-xl font-bold text-gray-800 mt-6 mb-4">Existing Residencies</h3>
                            @if ($residencies->isEmpty())
                                <p class="text-gray-600">No residencies found.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resident</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Council</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Household Size</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($residencies as $residency)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $residency->user->name ?? 'Unknown' }} ({{ $residency->user->email }})</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $residency->council->name ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $residency->collectorCompany->name ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">{{ $residency->household_size ?? 'N/A' }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <a href="{{ route('superadmin.residency.edit', $residency->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Council-Company Assignments Tab -->
                    <div id="council-companies" class="tab-pane hidden">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Assign Collector Company to Council</h3>
                            <form action="{{ route('superadmin.assign-collector-company') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="council_id" class="block text-gray-700 font-medium mb-2">Council</label>
                                    <select name="council_id" id="council_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                        <option value="">Select Council</option>
                                        @foreach ($councils as $council)
                                            <option value="{{ $council->id }}">{{ $council->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('council_id')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="collector_company_id" class="block text-gray-700 font-medium mb-2">Collection Company</label>
                                    <select name="collector_company_id" id="collector_company_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" required>
                                        <option value="">Select Company</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('collector_company_id')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold transition duration-200">Assign Company</button>
                            </form>
                            <h3 class="text-xl font-bold text-gray-800 mt-6 mb-4">Existing Assignments</h3>
                            @php
                                $assignments = \App\Models\Council::with('collectorCompanies')->get();
                            @endphp
                            @if ($assignments->isEmpty())
                                <p class="text-gray-600">No assignments found.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Council</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($assignments as $council)
                                                @foreach ($council->collectorCompanies as $company)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap">{{ $council->name }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap">{{ $company->name }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Reports Tab -->
                    <div id="reports" class="tab-pane hidden">
                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Collection Reports</h3>
                            <a href="{{ route('superadmin.reports') }}" class="text-blue-600 hover:underline mb-4 inline-block">View Detailed Reports</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

   <script>
document.addEventListener('DOMContentLoaded', () => {
    console.log('Tab script loaded');
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');

    if (tabButtons.length === 0) console.error('No tab buttons found');
    if (tabPanes.length === 0) console.error('No tab panes found');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tab = button.getAttribute('data-tab');
            console.log('Tab clicked:', tab);
            tabPanes.forEach(pane => pane.classList.add('hidden'));
            const targetPane = document.getElementById(tab);
            if (targetPane) {
                targetPane.classList.remove('hidden');
            } else {
                console.error('Tab pane not found:', tab);
            }
            tabButtons.forEach(btn => {
                btn.classList.remove('border-green-600', 'text-green-600');
                btn.classList.add('border-transparent', 'text-gray-600', 'hover:text-gray-800', 'hover:border-gray-300');
            });
            button.classList.remove('border-transparent', 'text-gray-600', 'hover:text-gray-800', 'hover:border-gray-300');
            button.classList.add('border-green-600', 'text-green-600');
        });
    });

    // Ensure Overview tab is visible by default
    const overviewTab = document.getElementById('overview');
    if (overviewTab) {
        overviewTab.classList.remove('hidden');
        console.log('Overview tab set visible');
    } else {
        console.error('Overview tab not found');
    }
});
</script>
@endsection