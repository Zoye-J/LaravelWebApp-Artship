<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-b from-cyan-900 via-orange-300 to-yellow-200 text-white">

    <!-- Navbar -->
<header class="flex justify-between items-center p-6">
    <div class="flex items-center">
        <img src="{{ asset('images/logo2.png') }}" alt="Artship Logo" class="h-16 w-16 mr-4"> 
        <h1 class="text-6xl font-bold">Artship</h1>
    </div>
    <div>
        <a href="{{ route('login') }}" class="px-4 py-2 bg-orange-300 text-black rounded-lg shadow hover:bg-gray-200">Login</a>
        <a href="{{ route('register') }}" class="ml-2 px-4 py-2 bg-red-300 text-black rounded-lg shadow hover:bg-gray-200">Register</a>
    </div>
</header>

    <!-- Hero Section -->
    <section class="flex flex-col items-center justify-center min-h-screen text-black text-center">
        <img src="{{ asset('images/background.png') }}" class="w-full h-96 object-cover rounded-xl shadow-lg mb-6">
        <h2 class="text-4xl font-bold mb-4">Welcome to Artship </h2>
        <p class="max-w-xl text-lg">The Ship will take you through your Art Journey. Become the Captain!</p>
    </section>

    <!-- About Section -->
    <section class="p-12  text-black">
        <h3 class="text-3xl font-bold mb-4">About Us</h3>
        <p class="max-w-2xl">Get on the ship and sail through, we'll sing shanties and fill the wooden ship with colors!</p>
    </section>

    <!-- Contact Section -->
    <footer class="p-12 bg-gray-900 text-orange-300">
        <h3 class="text-xl font-bold mb-4">Contact Us</h3>
        <p>Email: zoye.jahin@gmail.com</p>
        <p>Phone: +8801757467619</p>
    </footer>

</body>
</html>
