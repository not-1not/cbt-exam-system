<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'CBT Exam Portal') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS (via Tailwind Play CDN for immediate layout support) -->
    <script src="https://cdn.tailwindcss.com"></script>

    @livewireStyles
</head>
<body class="h-full font-sans antialiased bg-gray-50 text-gray-900 flex flex-col min-h-screen">
    @auth
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('exam.dashboard') }}" class="flex items-center space-x-2 text-indigo-600 font-bold text-xl tracking-tight">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>{{ config('app.name', 'CBT Exam') }}</span>
                        </a>
                        <nav class="hidden md:flex space-x-4 ml-6">
                            <a href="{{ route('exam.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('exam.dashboard') ? 'text-indigo-600 bg-indigo-50 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                                Dashboard Ujian
                            </a>
                            @if(auth()->user()->hasAnyRole(['admin', 'proctor', 'teacher']))
                                <a href="/admin" class="px-3 py-2 rounded-md text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100">
                                    Panel Admin
                                </a>
                            @endif
                        </nav>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-500 capitalize">{{ auth()->user()->roles->pluck('name')->join(', ') ?: 'Peserta' }}</div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
    @endauth

    @if (session('status'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-4 rounded-md bg-green-50 border border-green-200 text-sm text-green-800">
                {{ session('status') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-4 rounded-md bg-red-50 border border-red-200 text-sm text-red-800">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <main class="flex-grow">
        {{ $slot }}
    </main>

    <footer class="bg-white border-t border-gray-200 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} {{ config('app.name', 'CBT Exam Portal') }}. All rights reserved.
        </div>
    </footer>

    @livewireScripts
</body>
</html>
