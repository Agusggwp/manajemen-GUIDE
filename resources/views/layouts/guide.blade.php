<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F4F3FA]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Guide') - {{ $appName ?? 'ARTDEVATA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full antialiased font-sans text-[#29283D] bg-[#F4F3FA]">
    <div class="max-w-md mx-auto min-h-screen bg-[#F4F3FA] flex flex-col shadow-[0_0_30px_rgba(55,48,100,0.06)]">
        <!-- Top App Bar (Claymorphism Purple) -->
        <header class="sticky top-0 z-30 bg-gradient-to-r from-[#6C63FF] to-[#5850E6] text-white px-4 py-3.5 shadow-[0_6px_20px_rgba(108,99,255,0.28)]">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    @if(!empty($appLogo) && file_exists(public_path($appLogo)))
                        <img src="{{ asset($appLogo) }}" alt="{{ $appName }}" class="w-8 h-8 object-contain rounded-xl bg-white/15 p-0.5 shadow-xs shrink-0">
                    @else
                        <div class="w-8 h-8 rounded-xl bg-white/20 text-[#FFD166] font-extrabold flex items-center justify-center text-sm shadow-xs shrink-0">
                            {{ $appShortName ?? substr($appName ?? 'A', 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <div class="text-[10px] text-[#FFD166] font-bold tracking-wider uppercase">{{ $guidePortalTitle ?? 'Portal Pemandu Wisata' }}</div>
                        <div class="text-sm font-bold text-white leading-tight">{{ auth()->user()->name }}</div>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    @if(auth()->user()->guide)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-[#FFD166] text-[#29283D] shadow-xs">
                        #{{ auth()->user()->guide->queue_number }}
                    </span>
                    @endif

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Keluar" class="p-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 px-4 pt-4 pb-28 sm:pb-32" style="padding-bottom: calc(7.5rem + env(safe-area-inset-bottom, 0px));">
            <!-- Flash Message -->
            @if(session('success'))
            <div class="mb-4 p-3.5 rounded-2xl bg-white border border-[#72D6C9]/50 text-[#1F7368] text-xs font-semibold flex items-center justify-between shadow-[6px_8px_20px_rgba(55,48,100,0.06)]">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded-lg bg-[#EBFBF8] text-[#72D6C9] flex items-center justify-center text-xs"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-[#1F7368]/60 hover:text-[#1F7368] font-bold">×</button>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 p-3.5 rounded-2xl bg-white border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-[6px_8px_20px_rgba(55,48,100,0.06)]">
                <div class="flex items-center gap-2">
                    <span class="w-5 h-5 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">!</span>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800 font-bold">×</button>
            </div>
            @endif

            @yield('content')
        </main>

        <!-- Bottom Navigation Bar (Mobile Clay Bar) -->
        <nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white/95 backdrop-blur-md border-t border-[#E8E6F0] rounded-t-3xl z-40 flex items-center justify-around py-2 px-2 shadow-[0_-8px_25px_rgba(55,48,100,0.08)]" style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom, 0px));">
            <a href="{{ route('guide.dashboard') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition-all {{ request()->routeIs('guide.dashboard') ? 'text-[#6C63FF] font-bold bg-[#ECEBFC]' : 'text-[#77768C] hover:text-[#29283D]' }}">
                <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('guide.dashboard') ? 'text-[#6C63FF]' : 'text-[#77768C]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-[10px]">Hari Ini</span>
            </a>

            <a href="{{ route('guide.queue') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition-all {{ request()->routeIs('guide.queue') ? 'text-[#6C63FF] font-bold bg-[#ECEBFC]' : 'text-[#77768C] hover:text-[#29283D]' }}">
                <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('guide.queue') ? 'text-[#6C63FF]' : 'text-[#77768C]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span class="text-[10px]">Antrean</span>
            </a>

            <a href="{{ route('guide.jobs') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition-all {{ request()->routeIs('guide.jobs') ? 'text-[#6C63FF] font-bold bg-[#ECEBFC]' : 'text-[#77768C] hover:text-[#29283D]' }}">
                <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('guide.jobs') ? 'text-[#6C63FF]' : 'text-[#77768C]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="text-[10px]">Tugas</span>
            </a>

            <a href="{{ route('guide.earnings') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition-all {{ request()->routeIs('guide.earnings') ? 'text-[#6C63FF] font-bold bg-[#ECEBFC]' : 'text-[#77768C] hover:text-[#29283D]' }}">
                <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('guide.earnings') ? 'text-[#6C63FF]' : 'text-[#77768C]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-[10px]">Gaji/Fee</span>
            </a>

            <a href="{{ route('guide.profile') }}" 
               class="flex flex-col items-center py-1.5 px-3 rounded-2xl transition-all {{ request()->routeIs('guide.profile') ? 'text-[#6C63FF] font-bold bg-[#ECEBFC]' : 'text-[#77768C] hover:text-[#29283D]' }}">
                <svg class="w-5 h-5 mb-0.5 {{ request()->routeIs('guide.profile') ? 'text-[#6C63FF]' : 'text-[#77768C]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="text-[10px]">Profil</span>
            </a>
        </nav>
    </div>

    @include('partials.sweetalert')
    @stack('scripts')
</body>
</html>
