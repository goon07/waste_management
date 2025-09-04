@extends('layouts.app')

@section('content')
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="max-w-md mx-auto bg-white rounded-xl shadow-md p-8">
                <div class="flex justify-between mb-6">
                    <button id="signin-tab" class="w-1/2 py-2 text-center text-gray-700 font-bold border-b-2 border-transparent hover:border-green-600 active:border-green-600" onclick="toggleForm('signin')">Sign In</button>
                    <button id="signup-tab" class="w-1/2 py-2 text-center text-gray-700 font-bold border-b-2 border-transparent hover:border-green-600" onclick="toggleForm('signup')">Sign Up</button>
                </div>

                <!-- Sign In Form -->
                <div id="signin-form" class="form-content">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Sign In</h2>
                    @if (session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    @if (session('status'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="mb-6">
                            <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            @error('email')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-6">
                            <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                            <input type="password" name="password" id="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            @error('password')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-center mb-6">
                            <input type="checkbox" name="remember" id="remember" class="mr-2">
                            <label for="remember" class="text-gray-700">Remember Me</label>
                        </div>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold transition duration-300">
                            Sign In
                        </button>
                    </form>
                    <p class="text-center text-gray-600 mt-4">
                        <a href="{{ route('password.request') }}" class="text-green-600 hover:underline">Forgot Password?</a>
                    </p>
                </div>

                <!-- Sign Up Form -->
                <div id="signup-form" class="form-content hidden">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Sign Up</h2>
                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        <div class="mb-6">
                            <label for="name" class="block text-gray-700 font-medium mb-2">Name</label>
                            <input type="text" name="name" id="name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            @error('name')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-6">
                            <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                            <input type="email" name="email" id="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            @error('email')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-6">
                            <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                            <input type="password" name="password" id="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            @error('password')
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-6">
                            <label for="password_confirmation" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                        </div>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold transition duration-300">
                            Sign Up
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        function toggleForm(form) {
            const signinForm = document.getElementById('signin-form');
            const signupForm = document.getElementById('signup-form');
            const signinTab = document.getElementById('signin-tab');
            const signupTab = document.getElementById('signup-tab');

            if (form === 'signin') {
                signinForm.classList.remove('hidden');
                signupForm.classList.add('hidden');
                signinTab.classList.add('border-green-600');
                signupTab.classList.remove('border-green-600');
            } else {
                signupForm.classList.remove('hidden');
                signinForm.classList.add('hidden');
                signupTab.classList.add('border-green-600');
                signinTab.classList.remove('border-green-600');
            }
        }

        toggleForm('signin');
    </script>
@endsection