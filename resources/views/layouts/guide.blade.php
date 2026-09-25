<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Guide') - {{ $appName ?? 'ARTDEVATA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full antialiased font-sans text-slate-900 bg-slate-50 pb-20">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 flex flex-col shadow-md">
        <!-- Top App Bar -->
        <header class="sticky top-0 z-30 bg-[#14433b] text-white px-4 py-3.5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    @if(!empty($appLogo) && file_exists(public_path($appLogo)))
                        <img src="{{ asset($appLogo) }}" alt="{{ $appName }}" class="w-8 h-8 object-contain rounded-lg bg-white/10 p-0.5 shadow-xs shrink-0">
                    @else
                        <div class="w-8 h-8 rounded-lg bg-[#21c9a4] text-[#14433b] font-bold flex items-center justify-center text-sm shadow-xs shrink-0">
                            {{ $appShortName ?? substr($appName ?? 'A', 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <div class="text-xs text-[#21c9a4] font-medium tracking-wide uppercase">{{ $guidePortalTitle ?? 'Portal Pemandu Wisata' }}</div>
                        <div class="text-sm font-bold text-white">{{ auth()->user()->name }}</div>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    @if(auth()->user()->guide)
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-[#21c9a4] text-[#14433b] shadow-xs">
                        #{{ auth()->user()->guide->queue_number }}
                    </span>
                    @endif

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Keluar" class="p-1 rounded text-white/70 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 p-4">
            <!-- Flash Message -->
            @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-[#e6faf5] border border-[#21c9a4]/40 text-[#14433b] text-xs font-medium flex items-center justify-between shadow-xs">
                <span>{{ session('success') }}</span>
                <button type="button" @click="$el.parentElement.remove()" class="text-[#14433b]/60">×</button>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center justify-between shadow-xs">
                <span>{{ session('error') }}</span>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-600">×</button>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- Bottom Navigation Bar (Mobile) -->
        <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white border-t border-slate-200 z-40 flex items-center justify-around py-2 px-3 shadow-lg">
            <a href="{{ route('guide.dashboard') }}" 
               class="flex flex-col items-center py-1 px-2 text-[11px] font-medium {{ request()->routeIs('guide.dashboard') ? 'text-[#14433b] font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('guide.dashboard') ? 'text-[#21c9a4]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Hari Ini</span>
            </a>

            <a href="{{ route('guide.queue') }}" 
               class="flex flex-col items-center py-1 px-2 text-[11px] font-medium {{ request()->routeIs('guide.queue') ? 'text-[#14433b] font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('guide.queue') ? 'text-[#21c9a4]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Antrean</span>
            </a>

            <a href="{{ route('guide.jobs') }}" 
               class="flex flex-col items-center py-1 px-2 text-[11px] font-medium {{ request()->routeIs('guide.jobs') ? 'text-[#14433b] font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('guide.jobs') ? 'text-[#21c9a4]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Tugas</span>
            </a>

            <a href="{{ route('guide.earnings') }}" 
               class="flex flex-col items-center py-1 px-2 text-[11px] font-medium {{ request()->routeIs('guide.earnings') ? 'text-[#14433b] font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('guide.earnings') ? 'text-[#21c9a4]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Gaji/Fee</span>
            </a>

            <a href="{{ route('guide.profile') }}" 
               class="flex flex-col items-center py-1 px-2 text-[11px] font-medium {{ request()->routeIs('guide.profile') ? 'text-[#14433b] font-bold' : 'text-slate-400 hover:text-slate-600' }}">
                <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('guide.profile') ? 'text-[#21c9a4]' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Profil</span>
            </a>
        </nav>
    </div>

    @include('partials.sweetalert')
    @stack('scripts')
</body>
</html>
