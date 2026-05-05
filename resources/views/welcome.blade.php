<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Villa Estela Fine Inn</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f7f7;
        }
    </style>
</head>
<body class="text-[#989B88] antialiased flex flex-col min-h-screen">

    <!-- Navigation Bar -->
    <nav class="sticky top-0 z-50 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="#" class="text-2xl font-bold text-[#B6424F]">Villa Estela</a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="#" class="text-[#989B88] hover:text-[#B6424F] font-medium transition-colors">Home</a>
                    <a href="#rooms" class="text-[#989B88] hover:text-[#B6424F] font-medium transition-colors">Rooms</a>
                    <a href="#amenities" class="text-[#989B88] hover:text-[#B6424F] font-medium transition-colors">Amenities</a>
                    <a href="#contact" class="text-[#989B88] hover:text-[#B6424F] font-medium transition-colors">Contact</a>
                    
                    @if (Route::has('login'))
                        <div class="flex items-center space-x-4 border-l border-[#9FAAAC] pl-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-[#989B88] hover:text-[#B6424F] font-medium">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="text-[#989B88] hover:text-[#B6424F] font-medium">Log in</a>
                            @endauth
                        </div>
                    @endif
                    
                    <a href="#book" class="bg-[#B6424F] hover:bg-[#B57D59] text-white px-6 py-2.5 rounded-md font-semibold transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        Book Now
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button class="text-[#989B88] hover:text-[#B6424F] focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative bg-gray-900 h-[80vh] min-h-[600px] flex items-center justify-center">
        <!-- Hero Background -->
        <div class="absolute inset-0 w-full h-full">
            <img src="{{ asset('images/hero.jpg') }}" alt="Villa Estela Fine Inn" class="w-full h-full object-cover opacity-60 mix-blend-overlay" onerror="this.src='https://images.unsplash.com/photo-1542314831-c6a4d27ce6a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80'" />
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
        </div>

        <div class="relative z-10 text-center px-4 sm:px-6 lg:px-8 w-full max-w-5xl mx-auto mt-[-100px]">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-6 drop-shadow-md">
                Comfort & Convenience at Villa Estela
            </h1>
            <p class="text-lg sm:text-xl text-gray-200 mb-10 max-w-2xl mx-auto drop-shadow">
                Experience a welcoming and accessible stay with genuine hospitality and top-notch modern amenities.
            </p>
        </div>

        <!-- Booking Bar (Overlapping Hero) -->
        <div id="book" class="absolute -bottom-16 left-0 right-0 w-full px-4 sm:px-6 lg:px-8 z-20">
            <div class="max-w-5xl mx-auto bg-white rounded-xl shadow-xl p-4 sm:p-6 border border-gray-100">
                <form class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-semibold text-[#9FAAAC] mb-1">Check-in</label>
                        <input type="date" class="w-full border border-[#9FAAAC]/40 rounded-md px-4 py-2.5 text-[#1b1b18] focus:ring-2 focus:ring-[#B6424F] focus:border-transparent outline-none">
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-semibold text-[#9FAAAC] mb-1">Check-out</label>
                        <input type="date" class="w-full border border-[#9FAAAC]/40 rounded-md px-4 py-2.5 text-[#1b1b18] focus:ring-2 focus:ring-[#B6424F] focus:border-transparent outline-none">
                    </div>
                    <div class="w-full md:w-1/5">
                        <label class="block text-sm font-semibold text-[#9FAAAC] mb-1">Guests</label>
                        <select class="w-full border border-[#9FAAAC]/40 rounded-md px-4 py-2.5 text-[#1b1b18] focus:ring-2 focus:ring-[#B6424F] focus:border-transparent outline-none bg-white">
                            <option>1 Guest</option>
                            <option>2 Guests</option>
                            <option>3 Guests</option>
                            <option>4+ Guests</option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/5">
                        <label class="block text-sm font-semibold text-[#9FAAAC] mb-1">Rooms</label>
                        <select class="w-full border border-[#9FAAAC]/40 rounded-md px-4 py-2.5 text-[#1b1b18] focus:ring-2 focus:ring-[#B6424F] focus:border-transparent outline-none bg-white">
                            <option>1 Room</option>
                            <option>2 Rooms</option>
                            <option>3+ Rooms</option>
                        </select>
                    </div>
                    <div class="w-full md:w-auto md:flex-1">
                        <button type="button" class="w-full bg-[#B6424F] hover:bg-[#B57D59] text-white px-6 py-2.5 rounded-md font-semibold transition-all duration-300 shadow-md">
                            Check Availability
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-24 pb-16">
        
        <!-- Services / Amenities Section -->
        <section id="amenities" class="py-16 bg-[#f7f7f7]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-[#b6424f] mb-4">Our Premium Amenities</h2>
                    <p class="text-[#989B88] max-w-2xl mx-auto">Everything you need for a comfortable and convenient stay.</p>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Amenity 1 -->
                    <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow text-center border border-[#9FAAAC]/20 group">
                        <div class="inline-flex items-center justify-center p-4 bg-[#B6424F]/10 rounded-full mb-4 text-[#B6424F] group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Free Wi-Fi</h3>
                        <p class="text-sm text-[#989B88]">High-speed internet access in all areas.</p>
                    </div>
                    <!-- Amenity 2 -->
                    <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow text-center border border-[#9FAAAC]/20 group">
                        <div class="inline-flex items-center justify-center p-4 bg-[#B6424F]/10 rounded-full mb-4 text-[#B6424F] group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Secure Parking</h3>
                        <p class="text-sm text-[#989B88]">Complimentary private parking on-site.</p>
                    </div>
                    <!-- Amenity 3 -->
                    <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow text-center border border-[#9FAAAC]/20 group">
                        <div class="inline-flex items-center justify-center p-4 bg-[#B6424F]/10 rounded-full mb-4 text-[#B6424F] group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">24/7 Reception</h3>
                        <p class="text-sm text-[#989B88]">Always here to assist you anytime.</p>
                    </div>
                    <!-- Amenity 4 -->
                    <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow text-center border border-[#9FAAAC]/20 group">
                        <div class="inline-flex items-center justify-center p-4 bg-[#B6424F]/10 rounded-full mb-4 text-[#B6424F] group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Room Service</h3>
                        <p class="text-sm text-[#989B88]">Daily housekeeping & on-demand service.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Rooms Section -->
        <section id="rooms" class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-end mb-10 gap-4">
                    <div>
                        <h2 class="text-3xl font-bold text-[#b6424f] mb-2">Featured Rooms</h2>
                        <p class="text-[#989B88]">Choose the perfect space for your getaway.</p>
                    </div>
                    <a href="#rooms" class="text-[#B57D59] font-semibold hover:text-[#B6424F] transition-colors flex items-center">
                        View All Rooms 
                        <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Room Card 1 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-[#9FAAAC]/20 group hover:shadow-xl transition-all duration-300">
                        <div class="relative h-64 overflow-hidden">
                            <div class="absolute inset-0 bg-gray-200"></div>
                            <img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Standard Room" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-4 right-4 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-sm font-bold text-[#B6424F]">
                                $85 / night
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Standard Deluxe Room</h3>
                            <p class="text-[#989B88] text-sm mb-4 line-clamp-2">A cozy and practical room designed for passing travelers featuring a comfortable queen bed and modern amenities.</p>
                            
                            <div class="flex items-center gap-4 mb-6 text-sm text-[#9FAAAC]">
                                <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> 2 Guests</span>
                                <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg> 25 m²</span>
                            </div>
                            
                            <button class="w-full border-2 border-[#B6424F] text-[#B6424F] hover:bg-[#B6424F] hover:text-white font-semibold py-2.5 rounded-md transition-colors duration-300">
                                Book Now
                            </button>
                        </div>
                    </div>

                    <!-- Room Card 2 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-[#9FAAAC]/20 group hover:shadow-xl transition-all duration-300">
                        <div class="relative h-64 overflow-hidden">
                            <div class="absolute inset-0 bg-gray-200"></div>
                            <img src="https://images.unsplash.com/photo-1582719478250-c89af14fbcee?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Executive Suite" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-4 right-4 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-sm font-bold text-[#B6424F]">
                                $120 / night
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Executive Suite</h3>
                            <p class="text-[#989B88] text-sm mb-4 line-clamp-2">Spacious accommodation with a king-sized bed, private sitting area, and premium bath amenities for total relaxation.</p>
                            
                            <div class="flex items-center gap-4 mb-6 text-sm text-[#9FAAAC]">
                                <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> 2-3 Guests</span>
                                <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg> 40 m²</span>
                            </div>
                            
                            <button class="w-full border-2 border-[#B6424F] text-[#B6424F] hover:bg-[#B6424F] hover:text-white font-semibold py-2.5 rounded-md transition-colors duration-300">
                                Book Now
                            </button>
                        </div>
                    </div>

                    <!-- Room Card 3 -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-[#9FAAAC]/20 group hover:shadow-xl transition-all duration-300">
                        <div class="relative h-64 overflow-hidden">
                            <div class="absolute inset-0 bg-gray-200"></div>
                            <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Family Room" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-4 right-4 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-sm font-bold text-[#B6424F]">
                                $150 / night
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Family Connecting Room</h3>
                            <p class="text-[#989B88] text-sm mb-4 line-clamp-2">Ideal for groups or families, featuring multiple beds, extra space, and inclusive complimentary breakfast.</p>
                            
                            <div class="flex items-center gap-4 mb-6 text-sm text-[#9FAAAC]">
                                <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> 4-5 Guests</span>
                                <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg> 55 m²</span>
                            </div>
                            
                            <button class="w-full border-2 border-[#B6424F] text-[#B6424F] hover:bg-[#B6424F] hover:text-white font-semibold py-2.5 rounded-md transition-colors duration-300">
                                Book Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- About Overview -->
        <section class="py-16 bg-[#f7f7f7]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-sm border border-[#9FAAAC]/20 overflow-hidden flex flex-col md:flex-row">
                    <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
                        <h2 class="text-3xl font-bold text-[#B6424F] mb-4">Welcome to Villa Estela</h2>
                        <p class="text-gray-600 mb-6 leading-relaxed">
                            A highly accessible roadside inn designed with structure, warmth, and complete comfort in mind. At Villa Estela, we pride ourselves on providing a hospitable atmosphere wrapped in a modern, practical aesthetic. Whether you're making a quick stopover or planning a dedicated trip, our doors are always open to offer you an exceptional stay.
                        </p>
                        <a href="#contact" class="inline-flex items-center text-[#B57D59] font-medium hover:text-[#B6424F] transition-colors">
                            Learn more about our story <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </a>
                    </div>
                    <div class="md:w-1/2 bg-gray-200">
                        <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Hotel Interior" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer id="contact" class="bg-[#1b1b18] text-white pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12 border-b border-gray-800 pb-12">
                <div class="md:col-span-2">
                    <h3 class="text-2xl font-bold text-[#B6424F] mb-4">Villa Estela Fine Inn</h3>
                    <p class="text-gray-400 mb-6 max-w-sm">Experience the ideal blend of comfort, style, and accessibility. Your perfect roadside retreat.</p>
                    <div class="flex space-x-4">
                        <!-- Social Icons -->
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-[#B6424F] hover:text-white transition-colors">
                            <span class="sr-only">Facebook</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-[#B6424F] hover:text-white transition-colors">
                            <span class="sr-only">Instagram</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold text-white mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-[#B6424F] transition-colors">Home</a></li>
                        <li><a href="#rooms" class="text-gray-400 hover:text-[#B6424F] transition-colors">Our Rooms</a></li>
                        <li><a href="#amenities" class="text-gray-400 hover:text-[#B6424F] transition-colors">Amenities</a></li>
                        <li><a href="#contact" class="text-gray-400 hover:text-[#B6424F] transition-colors">Contact Us</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-bold text-white mb-4">Contact Info</h4>
                    <ul class="space-y-4 text-gray-400 text-sm">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-[#B57D59] mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>123 Highway Road, Main Ave,<br />City, Province 12345</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-[#B57D59] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            <span>+1 234 567 890</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-[#B57D59] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span>reservations@villaestela.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} Villa Estela Fine Inn. All rights reserved.</p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
    
</body>
</html>
