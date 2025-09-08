@auth
    <nav class="bg-white shadow-md" x-data="{ open: false }">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ auth()->user()->role === 'resident' ? route('resident.dashboard') : (auth()->user()->role === 'council_admin' ? route('council.dashboard') : route('collector.routes')) }}" class="text-2xl font-bold text-green-600">Waste Management</a>
                </div>

                <!-- Desktop -->
                <div class="hidden md:flex md:items-center md:space-x-6">
                     @if (auth()->user()->role === 'superadmin')
                     <a href="{{ route('map') }}"  class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('map') ? 'border-b-2 border-green-600' : '' }}">Map</a>
                    @elseif (auth()->user()->role === 'resident')
                        <a href="{{ route('resident.dashboard') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('resident.dashboard') ? 'border-b-2 border-green-600' : '' }}" aria-current="{{ request()->routeIs('resident.dashboard') ? 'page' : 'false' }}">Dashboard</a>
                        <a href="{{ route('resident.services') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('resident.services') ? 'border-b-2 border-green-600' : '' }}" aria-current="{{ request()->routeIs('resident.services') ? 'page' : 'false' }}">Services</a>
                        <a href="{{ route('resident.payments') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('resident.payments') ? 'border-b-2 border-green-600' : '' }}" aria-current="{{ request()->routeIs('resident.payments') ? 'page' : 'false' }}">Payments</a>
                        <a href="{{ route('resident.waste_guide') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('resident.waste_guide') ? 'border-b-2 border-green-600' : '' }}" aria-current="{{ request()->routeIs('resident.waste_guide') ? 'page' : 'false' }}">Waste Guide</a>
                        <a href="{{ route('resident.profile') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('resident.profile') ? 'border-b-2 border-green-600' : '' }}" aria-current="{{ request()->routeIs('resident.profile') ? 'page' : 'false' }}">Profile</a>
                    @elseif (auth()->user()->role === 'council_admin')
                        <a href="{{ route('council.dashboard') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('council.dashboard') ? 'border-b-2 border-green-600' : '' }}" aria-current="{{ request()->routeIs('council.dashboard') ? 'page' : 'false' }}">Dashboard</a>
                        <a href="{{ route('council.pickups') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('council.pickups') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('council.pickups') ? 'page' : 'false' }}">Pickups</a>
                        <a href="{{ route('council.users') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('council.users') ? 'border-b-2 border-green-600' : '' }}" aria-current="{{ request()->routeIs('council.users') ? 'page' : 'false' }}">Users</a>
                        <a href="{{ route('council.collectors') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('council.collectors') ? 'border-b-2 border-green-600' : '' }}" aria-current="{{ request()->routeIs('council.collectors') ? 'page' : 'false' }}">Collectors</a>
                        <a href="{{ route('council.issues') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('council.issues') ? 'border-b-2 border-green-600' : '' }}" aria-current="{{ request()->routeIs('council.issues') ? 'page' : 'false' }}">Issues</a>
                        <a href="{{ route('council.payments') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('council.payments') ? 'border-b-2 border-green-600' : '' }}" aria-current="{{ request()->routeIs('council.payments') ? 'page' : 'false' }}">Payments</a>
                        <a href="{{ route('council.reports') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('council.reports') ? 'border-b-2 border-green-600' : '' }}" aria-current="{{ request()->routeIs('council.reports') ? 'page' : 'false' }}">Reports</a>
                     <a href="{{ route('map') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium  {{ request()->routeIs('map') ? 'border-b-2 border-green-600' : '' }}"> Map</a>
                
                        @elseif (auth()->user()->role === 'collector')
                        <a href="{{ route('collector.routes') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('collector.routes') ? 'border-b-2 border-green-600' : '' }}" aria-current="{{ request()->routeIs('collector.routes') ? 'page' : 'false' }}">Collector Dashboard</a>
                  <a href="{{ route('map') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium  {{ request()->routeIs('map') ? 'border-b-2 border-green-600' : '' }}"> Map</a>
                        @elseif (auth()->user()->role === 'company_admin')
                        
                        
                         <a href="{{ route('management.dashboard') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('management.dashboard') ? 'border-b-2 border-green-600' : '' }}">Dashboard</a>
        <a href="{{ route('management.collectors') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('management.collectors') ? 'border-b-2 border-green-600' : '' }}">Collectors</a>
        <a href="{{ route('management.issues') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('management.issues') ? 'border-b-2 border-green-600' : '' }}">Issues</a>
        <a href="{{ route('management.residents') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('management.residents') ? 'border-b-2 border-green-600' : '' }}">Residents</a>
        <a href="{{ route('map') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium  {{ request()->routeIs('map') ? 'border-b-2 border-green-600' : '' }}"> Map</a>
                        @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-700 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium">Logout</button>
                    </form>
                </div>

                <!-- Mobile -->
                <div class="md:hidden flex items-center">
                    <button @click="open = !open" class="text-gray-700 hover:text-green-600 focus:outline-none" aria-label="Toggle navigation menu">
                        <i class="fas fa-bars text-2xl" x-show="!open" aria-hidden="true"></i>
                        <i class="fas fa-times text-2xl" x-show="open" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div x-show="open" x-cloak class="md:hidden">
                <div class="pt-2 pb-3 space-y-1">
                       @if (auth()->user()->role === 'superadmin')
                     <a href="{{ route('map') }}"  class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('map') ? 'border-b-2 border-green-600' : '' }}">Map</a>
                   
                    @elseif (auth()->user()->role === 'resident')
                        <a href="{{ route('resident.dashboard') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('resident.dashboard') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('resident.dashboard') ? 'page' : 'false' }}">Dashboard</a>
                        <a href="{{ route('resident.services') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('resident.services') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('resident.services') ? 'page' : 'false' }}">Services</a>
                        <a href="{{ route('resident.payments') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('resident.payments') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('resident.payments') ? 'page' : 'false' }}">Payments</a>
                        <a href="{{ route('resident.waste_guide') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('resident.waste_guide') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('resident.waste_guide') ? 'page' : 'false' }}">Waste Guide</a>
                        <a href="{{ route('resident.profile') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('resident.profile') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('resident.profile') ? 'page' : 'false' }}">Profile</a>
                    @elseif (auth()->user()->role === 'council_admin')
                        <a href="{{ route('council.dashboard') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('council.dashboard') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('council.dashboard') ? 'page' : 'false' }}">Dashboard</a>
                        <a href="{{ route('council.pickups') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('council.pickups') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('council.pickups') ? 'page' : 'false' }}">Pickups</a>
                        <a href="{{ route('council.users') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('council.users') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('council.users') ? 'page' : 'false' }}">Users</a>
                        <a href="{{ route('council.collectors') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('council.collectors') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('council.collectors') ? 'page' : 'false' }}">Collectors</a>
                        <a href="{{ route('council.issues') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('council.issues') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('council.issues') ? 'page' : 'false' }}">Issues</a>
                        <a href="{{ route('council.payments') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('council.payments') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('council.payments') ? 'page' : 'false' }}">Payments</a>
                        <a href="{{ route('council.reports') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('council.reports') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('council.reports') ? 'page' : 'false' }}">Reports</a>
                        <a href="{{ route('map') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium  {{ request()->routeIs('map') ? 'border-b-2 border-green-600' : '' }}"> Map</a>

                        @elseif (auth()->user()->role === 'collector')
                        <a href="{{ route('collector.routes') }}" class="block text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('collector.routes') ? 'border-l-4 border-green-600 bg-green-50' : '' }}" aria-current="{{ request()->routeIs('collector.routes') ? 'page' : 'false' }}">Collector Dashboard</a>
  <a href="{{ route('map') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium  {{ request()->routeIs('map') ? 'border-b-2 border-green-600' : '' }}"> Map</a>             
                        @elseif (auth()->user()->role === 'company_admin')
                        
                        
                         <a href="{{ route('management.dashboard') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('management.dashboard') ? 'border-b-2 border-green-600' : '' }}">Dashboard</a>
        <a href="{{ route('management.collectors') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('management.collectors') ? 'border-b-2 border-green-600' : '' }}">Collectors</a>
        <a href="{{ route('management.issues') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('management.issues') ? 'border-b-2 border-green-600' : '' }}">Issues</a>
        <a href="{{ route('management.residents') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('management.residents') ? 'border-b-2 border-green-600' : '' }}">Residents</a>
  <a href="{{ route('map') }}" class="text-gray-700 hover:text-green-600 px-3 py-2 rounded-md text-sm font-medium  {{ request()->routeIs('map') ? 'border-b-2 border-green-600' : '' }}"> Map</a>
                        @endif
                    <form action="{{ route('custom.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full text-left text-gray-700 hover:text-red-600 px-3 py-2 rounded-md text-base font-medium">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
@endauth