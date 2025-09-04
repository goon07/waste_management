<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MUTOTO Waste Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.10/dist/cdn.min.js" defer></script>
     @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
   <div class="min-h-screen bg-gray-100">
        @if (!request()->routeIs('index'))
            @include('layouts.navigation')
        @endif
        <main>@yield('content')</main>
    </div>
  
    @stack('scripts')
</body>
</html>