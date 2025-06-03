<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script type="module">
        import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.0.0/firebase-app.js';
        import { getAuth, signInWithPopup, GoogleAuthProvider } from 'https://www.gstatic.com/firebasejs/10.0.0/firebase-auth.js';

        const firebaseConfig = {
            apiKey: "{{ config('services.firebase_cfg.api_key') }}",
            authDomain: "{{ config('services.firebase_cfg.auth_domain') }}",
            projectId: "{{ config('services.firebase_cfg.project_id') }}",
            storageBucket: "{{ config('services.firebase_cfg.storage_bucket') }}",
            messagingSenderId: "{{ config('services.firebase_cfg.message_id') }}",
            appId: "{{ config('services.firebase_cfg.app_id') }}"
        };

        window.firebaseApp = initializeApp(firebaseConfig);
        window.firebaseAuth = getAuth(window.firebaseApp);
        window.GoogleAuthProvider = GoogleAuthProvider;
        window.signInWithPopup = signInWithPopup;
    </script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800">laravel-firebase App</a>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <span class="text-gray-700">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800">Login</a>
                        <a href="{{ route('register') }}" class="text-green-600 hover:text-green-800">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="py-8">
        @yield('content')
    </main>
</body>
</html>
