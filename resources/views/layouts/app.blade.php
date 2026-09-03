<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-b from-cyan-900 via-orange-300 to-yellow-200">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="text-gray-900">
                @yield('content')
            </main>
        </div>
    </body>
</html>
@if(auth()->check() && auth()->user()->role === 'admin')
<script>
// Auto-mark as viewed for admin pages
document.addEventListener('DOMContentLoaded', function() {
    // Submissions page
    if (window.location.pathname.includes('admin/submissions')) {
        fetch('/artwork/mark-all-viewed', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            document.getElementById('submissions-badge')?.remove();
        });
    }

    // Feedback page  
    if (window.location.pathname.includes('admin/feedback')) {
        fetch('/feedback/mark-all-viewed', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            document.getElementById('feedback-badge')?.remove();
        });
    }
});
</script>
@endif