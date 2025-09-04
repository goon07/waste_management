@extends('layouts.app')

@section('content')
<section class="py-12 bg-gray-100 min-h-screen" x-data="collectorCompanyPage()">
    <div class="container mx-auto px-4 max-w-7xl">
        <h1 class="text-4xl font-extrabold mb-8 text-gray-900">Collector Companies & Collectors</h1>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow" role="alert">
                {{ session('success') }}
            </div>
        @endif

        {{-- Add Company Button --}}
        <button @click="showCompanyModal = true; resetCompanyForm()" 
            class="mb-6 px-6 py-3 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            + Add Collector Company
        </button>

        {{-- Companies List --}}
        <div class="space-y-8">
            @foreach ($collectorCompanies as $company)
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-semibold text-gray-800">{{ $company->name }}</h2>
                        <div class="space-x-2">
                            <button @click="editCompany({{ $company->id }}, '{{ addslashes($company->name) }}')" 
                                class="text-indigo-600 hover:underline">Edit</button>
                        </div>
                    </div>

                    {{-- Collectors Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($company->collectors as $collector)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $collector->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $collector->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                            @if($collector->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm space-x-2">
                                            <button @click="editCollector({{ $collector->id }}, '{{ addslashes($collector->name) }}', '{{ $collector->email }}', {{ $collector->collector_company_id }})" 
                                                class="text-indigo-600 hover:underline">Edit</button>

                                            <form action="{{ route('council.collectors.deactivate', $collector->id) }}" method="POST" class="inline" 
                                                @submit.prevent="if(confirm('Change status for this collector?')) $el.submit()">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:underline">
                                                    {{ $collector->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No collectors found for this company.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Add Collector Button --}}
                    <button @click="showCollectorModal = true; resetCollectorForm(); collectorForm.collector_company_id = {{ $company->id }}" 
                        class="mt-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        + Add Collector
                    </button>
                </div>
            @endforeach
        </div>

        {{-- Company Modal --}}
        <div x-show="showCompanyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-transition>
            <div @click.away="showCompanyModal = false" class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6">
                <h3 class="text-xl font-bold mb-4" x-text="companyForm.id ? 'Edit Collector Company' : 'Add Collector Company'"></h3>
                <form :action="companyForm.id ? `{{ url('council/companies') }}/${companyForm.id}` : '{{ route('council.companies.store') }}'" 
                      method="POST" @submit.prevent="submitCompanyForm">
                    <template x-if="companyForm.id">
                        <input type="hidden" name="_method" value="PUT" />
                    </template>
                    @csrf
                    <div>
                        <label for="company_name" class="block font-medium text-gray-700 mb-1">Company Name</label>
                        <input type="text" id="company_name" name="name" x-model="companyForm.name" required
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    </div>
                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="button" @click="showCompanyModal = false" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
                        <button type="submit" class="px-6 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Collector Modal --}}
        <div x-show="showCollectorModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-transition>
            <div @click.away="showCollectorModal = false" class="bg-white rounded-lg shadow-lg max-w-lg w-full p-6">
                <h3 class="text-xl font-bold mb-4" x-text="collectorForm.id ? 'Edit Collector' : 'Add Collector'"></h3>
                <form :action="collectorForm.id ? `{{ url('council/collectors') }}/${collectorForm.id}` : '{{ route('council.collectors.store') }}'" 
                      method="POST" @submit.prevent="submitCollectorForm">
                    <template x-if="collectorForm.id">
                        <input type="hidden" name="_method" value="PUT" />
                    </template>
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="collector_name" class="block font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" id="collector_name" name="name" x-model="collectorForm.name" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                        <div>
                            <label for="collector_email" class="block font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="collector_email" name="email" x-model="collectorForm.email" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                        </div>
                        <div>
                            <label for="collector_company_id" class="block font-medium text-gray-700 mb-1">Collector Company</label>
                            <select id="collector_company_id" name="collector_company_id" x-model="collectorForm.collector_company_id" required
                                class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="" disabled>Select a company</option>
                                @foreach ($collectorCompanies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <template x-if="!collectorForm.id">
                            <div>
                                <label for="collector_password" class="block font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" id="collector_password" name="password" required
                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                            <div>
                                <label for="collector_password_confirmation" class="block font-medium text-gray-700 mb-1">Confirm Password</label>
                                <input type="password" id="collector_password_confirmation" name="password_confirmation" required
                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500" />
                            </div>
                        </template>
                    </div>
                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="button" @click="showCollectorModal = false" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</button>
                        <button type="submit" class="px-6 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function collectorCompanyPage() {
            return {
                showCompanyModal: false,
                showCollectorModal: false,
                companyForm: { id: null, name: '' },
                collectorForm: { id: null, name: '', email: '', collector_company_id: '', password: '', password_confirmation: '' },

                resetCompanyForm() {
                    this.companyForm = { id: null, name: '' };
                },
                editCompany(id, name) {
                    this.companyForm = { id, name };
                    this.showCompanyModal = true;
                },
                async submitCompanyForm(event) {
                    // Submit form normally (no AJAX here)
                    event.target.submit();
                },

                resetCollectorForm() {
                    this.collectorForm = { id: null, name: '', email: '', collector_company_id: '', password: '', password_confirmation: '' };
                },
                editCollector(id, name, email, companyId) {
                    this.collectorForm = { id, name, email, collector_company_id: companyId, password: '', password_confirmation: '' };
                    this.showCollectorModal = true;
                },
                async submitCollectorForm(event) {
                    // Submit form normally (no AJAX here)
                    event.target.submit();
                },
            }
        }
    </script>
@endsection