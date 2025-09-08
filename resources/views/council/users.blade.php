@extends('layouts.app')

@section('content')
<div x-data="{ tab: 'residents' }" class="p-6">
    <!-- Tabs -->
    <div class="flex space-x-4 mb-6">
        <button @click="tab = 'residents'" 
                :class="tab === 'residents' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
                class="px-4 py-2 rounded">Residents</button>
      
    </div>

    <!-- Residents Table -->
    <div x-show="tab === 'residents'" 
         x-data="residentsTable({{ Js::from($residents) }})" 
         class="mt-4">
        <div class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <input type="text" x-model="search" placeholder="Search residents..." 
                   class="border rounded px-2 py-1 w-full">

            <!-- Sort by Name -->
            <select x-model="nameOrder" class="border rounded px-2 py-1">
                <option value="">Sort by Name</option>
                <option value="asc">Name ↑</option>
                <option value="desc">Name ↓</option>
            </select>

            <!-- Filter by Status -->
            <select x-model="statusFilter" class="border rounded px-2 py-1">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <!-- Filter by Payment -->
            <select x-model="paymentFilter" class="border rounded px-2 py-1">
                <option value="">All Payments</option>
                <option value="paid">Paid</option>
                <option value="unpaid">Unpaid</option>
            </select>
        </div>

        <table class="w-full border-collapse border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">Name</th>
                    <th class="border px-2 py-1">Email</th>
                    <th class="border px-2 py-1">Address</th>
                    <th class="border px-2 py-1">Payment</th>
                    <th class="border px-2 py-1">Status</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="resident in paginatedResidents()" :key="resident.id">
                    <tr>
                        <td class="border px-2 py-1" x-text="resident.name"></td>
                        <td class="border px-2 py-1" x-text="resident.email"></td>
                        <td class="border px-2 py-1" 
                            x-text="resident.address"></td>
                        <td class="border px-2 py-1" 
                            x-text="resident.payment_status"></td>
                        <td class="border px-2 py-1" 
                            x-text="resident.user_status"></td>
                    </tr>
                </template>
                <tr x-show="filteredResidents.length === 0">
                    <td colspan="5" class="text-center p-4">No residents found</td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-4">
            <button @click="prevPage" :disabled="currentPage === 1" 
                    class="px-3 py-1 border rounded disabled:opacity-50">
                Prev
            </button>
            <span x-text="`Page ${currentPage} of ${totalPages()}`"></span>
            <button @click="nextPage" :disabled="currentPage === totalPages()" 
                    class="px-3 py-1 border rounded disabled:opacity-50">
                Next
            </button>
        </div>
    </div>

  

  
</div>

<script>
function residentsTable(initialResidents) {
    return {
        residents: initialResidents,
        search: '',
        currentPage: 1,
        perPage: 10,
        nameOrder: '',
        statusFilter: '',
        paymentFilter: '',

        get filteredResidents() {
            let data = this.residents;

            // 🔍 Search
            if (this.search) {
                const term = this.search.toLowerCase();
                data = data.filter(r =>
                    r.name.toLowerCase().includes(term) ||
                    r.email.toLowerCase().includes(term) ||
                    (r.residency?.address ?? '').toLowerCase().includes(term)
                );
            }

            // 🏷️ Filter by Status
            if (this.statusFilter) {
                data = data.filter(r => 
                    this.statusFilter === 'active' ? r.user_status: !r.user_status
                );
            }

            // 💳 Filter by Payment
            if (this.paymentFilter) {
                data = data.filter(r =>
                    this.paymentFilter === 'paid' ? r.payment_status : !r.payment_status
                );
            }

            // 🔼 Sort by Name
            if (this.nameOrder) {
                data = [...data].sort((a, b) => {
                    return this.nameOrder === 'asc' 
                        ? a.name.localeCompare(b.name) 
                        : b.name.localeCompare(a.name);
                });
            }

            return data;
        },

        paginatedResidents() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredResidents.slice(start, start + this.perPage);
        },

        totalPages() {
            return Math.ceil(this.filteredResidents.length / this.perPage) || 1;
        },

        nextPage() { if (this.currentPage < this.totalPages()) this.currentPage++; },
        prevPage() { if (this.currentPage > 1) this.currentPage--; }
    };
}
</script>
@endsection
