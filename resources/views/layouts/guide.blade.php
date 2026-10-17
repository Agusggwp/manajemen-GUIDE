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
    <div class="min-h-screen flex flex-col lg:flex-row bg-[#F4F3FA]">
        <!-- Desktop Sidebar (Tampil di layar Desktop >= lg) -->
        <aside class="hidden lg:flex flex-col w-64 bg-white border-r border-[#E8E6F0] shrink-0 sticky top-0 h-screen z-30 shadow-[4px_0_24px_rgba(55,48,100,0.02)]">
            <!-- Brand / Guide Info -->
            <div class="h-20 flex items-center px-6 gap-3.5 border-b border-[#E8E6F0]/80">
                @if(!empty($appLogo) && file_exists(public_path($appLogo)))
                    <img src="{{ asset($appLogo) }}" alt="{{ $appName }}" class="w-10 h-10 object-contain rounded-2xl bg-white p-1 shadow-sm shrink-0 border border-[#E8E6F0]">
                @else
                    <div class="w-10 h-10 rounded-2xl bg-[#6C63FF] text-white flex items-center justify-center font-black text-lg shadow-md shadow-[#6C63FF]/30 shrink-0">
                        {{ strtoupper(substr($appShortName ?? $appName ?? 'A', 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <div class="text-[9px] uppercase font-extrabold tracking-wider text-[#6C63FF] truncate">
                        {{ $guidePortalTitle ?? 'PORTAL PEMANDU WISATA' }}
                    </div>
                    <div class="text-base font-black text-[#29283D] truncate leading-tight">
                        {{ auth()->user()->name }}
                    </div>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                <a href="{{ route('guide.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs transition-all {{ request()->routeIs('guide.dashboard') ? 'bg-[#ECEBFC] text-[#6C63FF] font-extrabold shadow-xs' : 'text-[#77768C] hover:text-[#29283D] hover:bg-[#F4F3FA] font-bold' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Beranda</span>
                </a>

                <a href="{{ route('guide.queue') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs transition-all {{ request()->routeIs('guide.queue') ? 'bg-[#ECEBFC] text-[#6C63FF] font-extrabold shadow-xs' : 'text-[#77768C] hover:text-[#29283D] hover:bg-[#F4F3FA] font-bold' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Antrean</span>
                </a>

                <a href="{{ route('guide.jobs') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs transition-all {{ request()->routeIs('guide.jobs') ? 'bg-[#ECEBFC] text-[#6C63FF] font-extrabold shadow-xs' : 'text-[#77768C] hover:text-[#29283D] hover:bg-[#F4F3FA] font-bold' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Tugas</span>
                </a>

                <a href="{{ route('guide.earnings') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs transition-all {{ request()->routeIs('guide.earnings') ? 'bg-[#ECEBFC] text-[#6C63FF] font-extrabold shadow-xs' : 'text-[#77768C] hover:text-[#29283D] hover:bg-[#F4F3FA] font-bold' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Gaji/Fee</span>
                </a>

                <a href="{{ route('guide.profile') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-2xl text-xs transition-all {{ request()->routeIs('guide.profile') ? 'bg-[#ECEBFC] text-[#6C63FF] font-extrabold shadow-xs' : 'text-[#77768C] hover:text-[#29283D] hover:bg-[#F4F3FA] font-bold' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Profil</span>
                </a>
            </nav>

            <!-- Logout at bottom -->
            <div class="p-4 border-t border-[#E8E6F0]/80">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-bold text-[#77768C] hover:text-rose-600 hover:bg-rose-50 transition-colors">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Mobile Top App Bar (< lg) -->
            <header class="lg:hidden sticky top-0 z-30 bg-gradient-to-r from-[#6C63FF] to-[#5850E6] text-white px-4 py-3.5 shadow-md flex items-center justify-between">
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
            </header>

            <!-- Desktop Header Bar (>= lg) -->
            <header class="hidden lg:flex h-16 shrink-0 bg-white/80 backdrop-blur-md border-b border-[#E8E6F0] px-8 items-center justify-between sticky top-0 z-20">
                <div class="flex items-center gap-2.5 text-xs text-[#77768C]">
                    <span class="font-medium">{{ $guidePortalTitle ?? 'Portal Pemandu Wisata' }}</span>
                    <span class="text-slate-300">/</span>
                    <span class="font-extrabold text-[#29283D]">@yield('title', 'Beranda')</span>
                </div>

                <div class="flex items-center gap-4">
                    @if(auth()->user()->guide)
                    <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-[#6C63FF]/10 text-[#6C63FF] border border-[#6C63FF]/20">
                        <span class="w-2 h-2 rounded-full bg-[#6C63FF]"></span>
                        <span>Nomor Antrean: #{{ auth()->user()->guide->queue_number }}</span>
                    </div>
                    @endif

                    <!-- Notification Bell with red indicator dot -->
                    <button type="button" class="relative p-2 rounded-xl text-[#77768C] hover:text-[#6C63FF] hover:bg-[#ECEBFC] transition-colors" title="Notifikasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
                    </button>

                    <!-- User Avatar -->
                    <div class="flex items-center gap-2 pl-2 border-l border-[#E8E6F0]">
                        <div class="w-9 h-9 rounded-2xl bg-[#6C63FF] text-white font-extrabold text-sm flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <svg class="w-3.5 h-3.5 text-[#77768C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </header>

            <!-- Main Body -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 pb-28 lg:pb-8 bg-[#F4F3FA]">
                <!-- Flash Message -->
                @if(session('success'))
                <div class="mb-5 p-4 rounded-2xl bg-white border border-[#72D6C9]/50 text-[#1F7368] text-xs font-semibold flex items-center justify-between shadow-[6px_8px_20px_rgba(55,48,100,0.06)]">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-xl bg-[#EBFBF8] text-[#72D6C9] flex items-center justify-center text-xs font-bold shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-[#1F7368]/60 hover:text-[#1F7368] font-bold text-base px-1">×</button>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-5 p-4 rounded-2xl bg-white border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-[6px_8px_20px_rgba(55,48,100,0.06)]">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs font-bold shadow-2xs">!</span>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800 font-bold text-base px-1">×</button>
                </div>
                @endif

                @yield('content')
            </main>

            <!-- Bottom Navigation Bar (Tampil di Mobile < lg) -->
            <nav class="lg:hidden fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white/95 backdrop-blur-md border-t border-[#E8E6F0] rounded-t-3xl z-40 flex items-center justify-around py-2 px-2 shadow-[0_-8px_25px_rgba(55,48,100,0.08)]" style="padding-bottom: max(0.5rem, env(safe-area-inset-bottom, 0px));">
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
    </div>

    @include('partials.sweetalert')
    @stack('scripts')
</body>
</html>
