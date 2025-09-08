@extends('layouts.app')

@section('content')
    <!-- Header/Navigation -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-xl mr-3">
                    MW
                </div>
                <h1 class="text-2xl font-bold text-green-800">MUTOTO <span class="text-green-600">WASTE</span></h1>
            </div>
            
            <nav class="hidden md:flex space-x-8">
                <a href="#home" class="nav-link text-gray-700 hover:text-green-600 font-medium">Home</a>
                <a href="#services" class="nav-link text-gray-700 hover:text-green-600 font-medium">Services</a>
                <a href="#about" class="nav-link text-gray-700 hover:text-green-600 font-medium">About</a>
                <a href="#stats" class="nav-link text-gray-700 hover:text-green-600 font-medium">Impact</a>
                <a href="#contact" class="nav-link text-gray-700 hover:text-green-600 font-medium">Contact</a>
            </nav>
            
            <div class="hidden md:block">
                <a href="{{ route('signin') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-full font-medium transition duration-300">
                    Sign In
                </a>
            </div>
            
            <button id="mobile-menu-button" class="md:hidden text-gray-700">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
        
        <div id="mobile-menu" class="mobile-menu md:hidden bg-white">
            <div class="container mx-auto px-4 py-2 flex flex-col space-y-3">
                <a href="#home" class="nav-link text-gray-700 hover:text-green-600 font-medium py-2 border-b">Home</a>
                <a href="#services" class="nav-link text-gray-700 hover:text-green-600 font-medium py-2 border-b">Services</a>
                <a href="#about" class="nav-link text-gray-700 hover:text-green-600 font-medium py-2 border-b">About</a>
                <a href="#stats" class="nav-link text-gray-700 hover:text-green-600 font-medium py-2 border-b">Impact</a>
                <a href="#contact" class="nav-link text-gray-700 hover:text-green-600 font-medium py-2 border-b">Contact</a>
                <a href="{{ route('custom.signin') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-full font-medium my-2 transition duration-300">
                    Sign In
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero-gradient text-white py-20 bg-green-600 ">
        <div class="container mx-auto px-4 flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-10 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Smart Waste Management for a Cleaner Community</h1>
                <p class="text-xl mb-8">MUTOTO provides innovative waste collection and recycling solutions to create sustainable, eco-friendly neighborhoods.</p>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="{{ route('pickup') }}" class="bg-white text-green-700 hover:bg-gray-100 px-8 py-3 rounded-full font-bold transition duration-300">
                        Schedule Pickup
                    </a>
                    <a href="{{ route('learn-more') }}" class="border-2 border-white hover:bg-white hover:text-green-700 px-8 py-3 rounded-full font-bold transition duration-300">
                        Learn More
                    </a>
                </div>
            </div>
            <div class="md:w-1/2 flex justify-center">
                <img src="{{ asset('images/waste-management.jpg') }}" 
                     alt="Waste management" 
                     class="rounded-lg shadow-2xl max-w-full h-auto">
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Our Services</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Comprehensive waste management solutions tailored to your needs</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @if (isset($services) && is_array($services))
                    @foreach ($services as $service)
                        <div class="service-card bg-white rounded-xl shadow-md overflow-hidden p-6 transition duration-300">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
                                <i class="fas {{ $service['icon'] }} text-green-600 text-2xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 mb-3">{{ $service['title'] }}</h3>
                            <p class="text-gray-600 mb-4">{{ $service['description'] }}</p>
                            <a href="#" class="text-green-600 font-medium flex items-center">
                                Learn more <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    @endforeach
                @else
                    <p>Services data is not available.</p>
                @endif
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row items-center">
                <div class="lg:w-1/2 mb-10 lg:mb-0 lg:pr-10">
                    <img src="{{ asset('images/about.jpg') }}" 
                         alt="About MUTOTO Waste Management" 
                         class="rounded-lg shadow-xl w-full">
                </div>
                <div class="lg:w-1/2">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">About MUTOTO Waste Management</h2>
                    <p class="text-gray-600 mb-6 text-lg">
                        Founded in 2025, MUTOTO Waste Management has grown from a small community initiative to a leading waste management provider serving over 10,000 households and businesses across the country.
                    </p>
                    <p class="text-gray-600 mb-6 text-lg">
                        Our mission is to transform waste management through innovation, technology, and community engagement. We believe in creating sustainable solutions that benefit both the environment and the economy.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="flex items-start">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Certified Professionals</h4>
                                <p class="text-gray-600">Our team undergoes rigorous training in waste handling and safety protocols.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Eco-Friendly Fleet</h4>
                                <p class="text-gray-600">Our collection vehicles run on clean energy with minimal emissions.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Smart Technology</h4>
                                <p class="text-gray-600">Real-time tracking and route optimization for efficient service.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Community Focus</h4>
                                <p class="text-gray-600">We actively engage with local communities through education programs.</p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('learn-more') }}" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-full font-bold transition duration-300">
                        Our Story <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="py-20 bg-green-700 text-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4">Our Impact</h2>
                <p class="text-xl max-w-3xl mx-auto">Numbers that showcase our commitment to a cleaner, greener future</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach ([
                    ['value' => '10K+', 'label' => 'Tons Recycled'],
                    ['value' => '2K+', 'label' => 'Happy Customers'],
                    ['value' => '20+', 'label' => 'Communities Served'],
                    ['value' => '75%', 'label' => 'Waste Diverted from Landfills']
                ] as $stat)
                    <div class="stats-item bg-green-800 rounded-xl p-8 text-center">
                        <div class="text-5xl font-bold mb-3">{{ $stat['value'] }}</div>
                        <div class="text-xl">{{ $stat['label'] }}</div>
                        <div class="w-16 h-1 bg-green-500 mx-auto mt-4"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">What Our Customers Say</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Hear from the communities we serve</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ([
                    ['initials' => 'JM', 'name' => 'John Mbewe', 'role' => 'Residential Customer', 'quote' => 'MUTOTO\'s service has been consistently reliable. Their commitment to recycling has helped our household reduce waste by 60% in just six months.', 'rating' => 5],
                    ['initials' => 'AN', 'name' => 'Alice Nankamba', 'role' => 'Business Owner', 'quote' => 'As a restaurant owner, waste management was always a challenge. MUTOTO provided a customized solution that\'s both cost-effective and environmentally responsible.', 'rating' => 5],
                    ['initials' => 'RM', 'name' => 'Robert Mukelabayi', 'role' => 'Community Leader', 'quote' => 'MUTOTO\'s educational programs have transformed our neighborhood\'s approach to waste. Their team is professional, punctual, and truly cares about making a difference.', 'rating' => 4.5]
                ] as $testimonial)
                    <div class="testimonial-card bg-gray-50 rounded-xl p-8 shadow-sm">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 rounded-full bg-green-600 flex items-center justify-center text-white font-bold mr-4">{{ $testimonial['initials'] }}</div>
                            <div>
                                <h4 class="font-bold text-gray-800">{{ $testimonial['name'] }}</h4>
                                <p class="text-gray-600">{{ $testimonial['role'] }}</p>
                            </div>
                        </div>
                        <p class="text-gray-700 italic mb-6">{{ $testimonial['quote'] }}</p>
                        <div class="flex text-yellow-400">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fas {{ $i <= $testimonial['rating'] ? 'fa-star' : ($i <= ceil($testimonial['rating']) ? 'fa-star-half-alt' : 'fa-star') }}"></i>
                            @endfor
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row">
                <div class="lg:w-1/2 mb-10 lg:mb-0 lg:pr-10">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">Contact Us</h2>
                    <p class="text-gray-600 mb-8 text-lg">
                        Have questions about our services or want to schedule a pickup? Reach out to our team—we're happy to help!
                    </p>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <i class="fas fa-map-marker-alt text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Headquarters</h4>
                                <p class="text-gray-600">231 Kamwala South off Paul Ngozi Street, Lusaka</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <i class="fas fa-phone-alt text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Phone</h4>
                                <p class="text-gray-600">+260 (978) 125-598</p>
                                <p class="text-gray-600">Customer Service: Mon-Fri, 8AM-5PM</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <i class="fas fa-envelope text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800 mb-1">Email</h4>
                                <p class="text-gray-600">info@mutotowaste.com</p>
                                <p class="text-gray-600">support@mutotowaste.com</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-10">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Follow Us</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="w-10 h-10 bg-green-600 hover:bg-green-700 rounded-full flex items-center justify-center text-white">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-green-600 hover:bg-green-700 rounded-full flex items-center justify-center text-white">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-green-600 hover:bg-green-700 rounded-full flex items-center justify-center text-white">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="w-10 h-10 bg-green-600 hover:bg-green-700 rounded-full flex items-center justify-center text-white">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <div class="bg-white rounded-xl shadow-md p-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Send Us a Message</h3>
                        <form action="{{ route('contact.submit') }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="name" class="block text-gray-700 font-medium mb-2">Name</label>
                                    <input type="text" name="name" id="name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                                </div>
                                <div>
                                    <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                                    <input type="email" name="email" id="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                                </div>
                            </div>
                            <div class="mb-6">
                                <label for="subject" class="block text-gray-700 font-medium mb-2">Subject</label>
                                <input type="text" name="subject" id="subject" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            </div>
                            <div class="mb-6">
                                <label for="message" class="block text-gray-700 font-medium mb-2">Message</label>
                                <textarea name="message" id="message" rows="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required></textarea>
                            </div>
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold transition duration-300">
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-16 bg-green-800 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-6">Stay Updated</h2>
            <p class="text-xl mb-8 max-w-2xl mx-auto">Subscribe to our newsletter for waste management tips, company updates, and special offers.</p>
            <form action="{{ route('newsletter.subscribe') }}" method="POST">
                @csrf
                <div class="max-w-md mx-auto flex">
                    <input type="email" name="email" placeholder="Your email address" class="flex-grow px-4 py-3 rounded-l-lg focus:outline-none text-gray-800" required>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 px-6 py-3 rounded-r-lg font-bold transition duration-300">
                        Subscribe
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                <div>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-lg mr-3">
                            MW
                        </div>
                        <h3 class="text-xl font-bold">MUTOTO WASTE</h3>
                    </div>
                    <p class="text-gray-400 mb-6">
                        Leading the way in sustainable waste management solutions for communities and businesses throughout Zambia.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-6">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Services</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Pricing</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-6">Services</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white">Residential Collection</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Commercial Solutions</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Recycling Programs</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Composting</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Hazardous Waste</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-6">Legal</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Cookie Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Environmental Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Compliance</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 mb-4 md:mb-0">
                    &copy; 2025 MUTOTO Waste Management. All rights reserved.
                </p>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white">Privacy</a>
                    <a href="#" class="text-gray-400 hover:text-white">Terms</a>
                </div>
            </div>
        </div>
    </footer>
@endsection