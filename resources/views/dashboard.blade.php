<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Artship Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-b from-cyan-900 via-orange-300 to-yellow-200 text-amber-900 font-sans">

    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside class="w-64 bg-yellow-200 border-r border-orange-700 p-4">
            <h1 class="text-2xl text-center font-bold mb-6">Artship</h1>

            <!-- Profile Icon -->
            <div class="flex justify-center mb-4">
                <a href="{{ route('profile.show') }}">
                    <img src="{{ asset('images/profile.png') }}"
                        alt="Profile"
                        class="w-20 h-20 rounded-full border-4 border-amber-300 shadow">
                </a>
            </div>

            <nav class="flex flex-col space-y-4">
                <a href="{{ route('dashboard') }}" class="hover:bg-amber-200 p-2 rounded"> Dashboard</a>
                <a href="{{ route('courses.index') }}" class="hover:bg-amber-200 p-2 rounded"> Courses</a>  
                
                @if(auth()->check() && auth()->user()->role !== 'admin')
                    <a href="{{ route('my.courses') }}" class="hover:bg-amber-200 p-2 rounded"> My Courses</a>
                @endif
                @if(auth()->check() && auth()->user()->role !== 'admin')
                    <a href="{{ route('wishlist.index') }}" class="hover:bg-amber-200 p-2 rounded">Wishlist</a>
                @endif
                @if(auth()->check() && auth()->user()->role === 'admin')
                        @php
                            $pendingSubmissions = \App\Models\ArtworkSubmission::unviewed()->count();
                        @endphp

                        <a href="{{ route('artwork.index') }}" class="hover:bg-amber-200 p-2 rounded flex items-center">
                                Submissions
                            @if($pendingSubmissions > 0)
                                <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full" id="submissions-badge">
                                    {{ $pendingSubmissions }} new
                                </span>
                            @endif
                        </a>
                    
                    <!-- FEEDBACK LINK -->
            
                        @php
                            $newFeedback = \App\Models\CourseRating::unviewed()->count();
                        @endphp

                        <a href="{{ route('admin.feedback') }}" class="hover:bg-amber-200 p-2 rounded flex items-center">
                                Feedback
                            @if($newFeedback > 0)
                                <span class="ml-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full" id="feedback-badge">
                                    {{ $newFeedback }} new
                                </span>
                            @endif
                        </a>   
               
                @endif

            </nav>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="mt-6">
                @csrf
                <button type="submit" class="w-full text-left text-red-600 hover:bg-red-100 p-2 rounded">
                    Logout
                </button>
            </form>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <!-- Original Dashboard Content -->
            <div class="relative rounded overflow-hidden shadow-lg">
                <img src="{{ asset('images/dashboard-banner.png') }}" alt="Welcome Banner" class="w-full h-64 object-cover">
                <div class="absolute top-0 left-0 w-full h-full flex items-start justify-start p-7">
                    <div class="text-white font-bold text-6xl leading-tight drop-shadow-[2px_2px_5px_rgba(0,0,0,0.6)]">
                        <p>Welcome</p>
                        <p>To</p>
                        <p>Artship</p>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <p class="text-lg text-gray-900">Explore your courses and showcase your works!</p>
            </div>

            <!-- Featured Artworks Section -->
            @if(isset($featuredArtworks) && $featuredArtworks->count() > 0)
            <div class="mt-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 flex items-center">
                     Featured Community Artworks
                    <span class="ml-3 bg-orange-500 text-white px-3 py-1 rounded-full text-sm">
                        {{ $featuredArtworks->count() }} featured
                    </span>
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($featuredArtworks as $artwork)
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02]">
                            <!-- Artwork Image -->
                            <div class="relative">
                                <img src="{{ asset('storage/' . $artwork->image_path) }}" 
                                     alt="{{ $artwork->title }}"
                                     class="w-full h-64 object-cover">
                                
                                <!-- Featured Badge -->
                                <div class="absolute top-4 right-4 bg-yellow-500 text-white px-3 py-2 rounded-full text-sm font-semibold flex items-center">
                                    ⭐ Featured
                                </div>
                            </div>

                            <!-- Artwork Details -->
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ $artwork->title }}</h3>
                                
                                @if($artwork->description)
                                    <p class="text-gray-600 mb-4 line-clamp-2">{{ $artwork->description }}</p>
                                @endif
                                
                                <!-- Artist and Likes -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-r from-orange-500 to-amber-500 rounded-full flex items-center justify-center text-white font-semibold">
                                            {{ substr($artwork->user->name, 0, 1) }}
                                        </div>
                                        <span class="ml-3 text-sm text-gray-700 font-medium">{{ $artwork->user->name }}</span>
                                    </div>
                                    
                                    <!-- Like Button -->
                                    @auth
                                    <button onclick="toggleLike({{ $artwork->id }})" 
                                            class="like-btn flex items-center space-x-2 px-4 py-2 rounded-full border border-gray-200 hover:border-red-200 hover:bg-red-50 transition-all duration-200 {{ $artwork->isLikedByUser() ? 'bg-red-50 border-red-200' : '' }}"
                                            id="like-btn-{{ $artwork->id }}">
                                        <span class="text-lg {{ $artwork->isLikedByUser() ? 'text-red-500' : 'text-gray-400' }}">❤️</span>
                                        <span class="text-sm font-medium text-gray-700" id="like-count-{{ $artwork->id }}">
                                            {{ $artwork->likes_count }}
                                        </span>
                                    </button>
                                    @endauth
                                </div>

                                <!-- Course Info -->
                                <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl p-4">
                                    <p class="text-xs text-orange-700 font-semibold mb-1">CREATED IN COURSE</p>
                                    <p class="text-sm font-medium text-orange-800">{{ $artwork->course->title }}</p>
                                    <p class="text-xs text-orange-600 mt-1">
                                        {{ $artwork->created_at->format('M d, Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            <!-- Show this if no featured artworks or variable not set -->
            <div class="mt-12 bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="text-8xl mb-6">🎨</div>
                <h3 class="text-2xl font-semibold text-gray-700 mb-4">No featured artworks yet</h3>
                <p class="text-gray-500">Check back later to see amazing artwork from our community!</p>
            </div>
            @endif

            <!-- Like Functionality JavaScript -->
            @auth
            <script>
            function toggleLike(artworkId) {
                fetch(`/artwork/${artworkId}/like`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const likeBtn = document.getElementById(`like-btn-${artworkId}`);
                        const likeCount = document.getElementById(`like-count-${artworkId}`);
                        
                        likeCount.textContent = data.likes_count;
                        
                        if (data.is_liked) {
                            likeBtn.classList.add('bg-red-50', 'border-red-200');
                            likeBtn.querySelector('span').classList.add('text-red-500');
                            likeBtn.querySelector('span').classList.remove('text-gray-400');
                        } else {
                            likeBtn.classList.remove('bg-red-50', 'border-red-200');
                            likeBtn.querySelector('span').classList.remove('text-red-500');
                            likeBtn.querySelector('span').classList.add('text-gray-400');
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            }
            </script>
            @endauth

            <style>
            .line-clamp-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            </style>
        </main>
    </div>

</body>
</html>