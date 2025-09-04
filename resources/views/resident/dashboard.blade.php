@extends('layouts.app')

@section('content')
 <section class="py-12 bg-gray-50 min-h-screen">
 <div class="container mx-auto px-4 sm:px-6 lg:px-8">
 <div class="flex justify-between items-center mb-8">
 <h2 class="text-3xl font-bold text-gray-800">Welcome, {{ auth()->user()->name }}!</h2>
 <a href="{{ route('resident.services') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition duration-200" aria-label="Request a new pickup">Quick Pickup Request</a>
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

 <!-- Summary Widgets -->
 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
 <div class="bg-white rounded-xl shadow-md p-6 text-center">
 <h3 class="text-lg font-semibold text-gray-700">Next Pickup</h3>
 <p class="text-gray-600 mt-2">{{ $nextPickup ? $nextPickup->format('Y-m-d') : 'Not scheduled' }}</p>
 </div>
 <div class="bg-white rounded-xl shadow-md p-6 text-center">
 <h3 class="text-lg font-semibold text-gray-700">Open Issues</h3>
 <p class="text-gray-600 mt-2">{{ \App\Models\Issue::where('user_id', auth()->user()->id)->where('status', 'open')->count() }}</p>
 </div>
 <div class="bg-white rounded-xl shadow-md p-6 text-center">
 <h3 class="text-lg font-semibold text-gray-700">Last Pickup</h3>
 <p class="text-gray-600 mt-2">{{ $lastPickup ? $lastPickup->completed_date->format('Y-m-d') : 'N/A' }}</p>
 </div>
 <div class="bg-white rounded-xl shadow-md p-6 text-center">
 <h3 class="text-lg font-semibold text-gray-700">Collection Frequency</h3>
 <p class="text-gray-600 mt-2">{{ $residency && $residency->waste_collection_frequency ? ucfirst($residency->waste_collection_frequency) : 'Not set' }}</p>
 </div>
 </div>

 <!-- Navigation Cards -->
 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
 <a href="{{ route('resident.profile') }}" class="bg-white rounded-xl shadow-md p-6 text-center hover:bg-gray-50 hover:scale-105 transition-transform duration-200" aria-label="Navigate to resident profile">
 <i class="fas fa-user text-3xl text-green-600 mb-4" aria-hidden="true"></i>
 <h3 class="text-xl font-bold text-gray-800">Profile</h3>
 <p class="text-gray-600 mt-2">Manage your account and residency details</p>
 </a>
 <a href="{{ route('resident.services') }}" class="bg-white rounded-xl shadow-md p-6 text-center hover:bg-gray-50 hover:scale-105 transition-transform duration-200" aria-label="Navigate to resident services">
 <i class="fas fa-recycle text-3xl text-green-600 mb-4" aria-hidden="true"></i>
 <h3 class="text-xl font-bold text-gray-800">Services</h3>
 <p class="text-gray-600 mt-2">Schedule pickups and report issues</p>
 </a>
 <a href="{{ route('resident.payments') }}" class="bg-white rounded-xl shadow-md p-6 text-center hover:bg-gray-50 hover:scale-105 transition-transform duration-200" aria-label="Navigate to resident payments">
 <i class="fas fa-credit-card text-3xl text-green-600 mb-4" aria-hidden="true"></i>
 <h3 class="text-xl font-bold text-gray-800">Payments</h3>
 <p class="text-gray-600 mt-2">View and manage your payment history</p>
 </a>
 <a href="{{ route('resident.waste_guide') }}" class="bg-white rounded-xl shadow-md p-6 text-center hover:bg-gray-50 hover:scale-105 transition-transform duration-200" aria-label="Navigate to waste guide">
 <i class="fas fa-info-circle text-3xl text-green-600 mb-4" aria-hidden="true"></i>
 <h3 class="text-xl font-bold text-gray-800">Waste Guide</h3>
 <p class="text-gray-600 mt-2">Learn about waste management practices</p>
 </a>
 </div>
 </div>
 </section>
@endsection