<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ $appName ?? 'ARTDEVATA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
    <style>
        @view-transition { navigation: auto; }
        ::view-transition-group(root) { animation-duration: 0.1s; }
        ::view-transition-old(main-sidebar),
        ::view-transition-new(main-sidebar) {
            animation: none;
            mix-blend-mode: normal;
        }
        ::view-transition-old(top-navbar),
        ::view-transition-new(top-navbar) {
            animation: none;
            mix-blend-mode: normal;
        }

        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Stable Core Layout (Zero Layout Reflow / Zero Flicker) */
        .sidebar-container {
            width: 16rem !important; /* 256px / w-64 */
            view-transition-name: main-sidebar;
            flex-shrink: 0 !important;
        }
        .top-navbar {
            view-transition-name: top-navbar;
        }

        /* Collapsed Sidebar Rules (Instant 0ms pre-paint) */
        html.sidebar-collapsed .sidebar-container {
            width: 4.5rem !important; /* 72px */
        }
        html.sidebar-collapsed .sidebar-text {
            display: none !important;
        }
        html.sidebar-collapsed .sidebar-header {
            justify-content: center !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        html.sidebar-collapsed .sidebar-nav {
            padding: 0.5rem !important;
        }
        html.sidebar-collapsed .sidebar-group-title {
            display: none !important;
        }
        html.sidebar-collapsed .sidebar-group-divider {
            display: block !important;
        }
        html.sidebar-collapsed .sidebar-link {
            justify-content: center !important;
            padding: 0.625rem !important;
            height: 2.5rem !important;
            width: 2.5rem !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
        html.sidebar-collapsed .sidebar-link-content {
            margin: 0 !important;
        }
        html.sidebar-collapsed .sidebar-footer {
            padding: 0.75rem 0.25rem !important;
        }
        .sidebar-footer-collapsed {
            display: none !important;
        }
        .sidebar-footer-expanded {
            display: flex !important;
        }
        html.sidebar-collapsed .sidebar-footer-collapsed {
            display: flex !important;
        }
        html.sidebar-collapsed .sidebar-footer-expanded {
            display: none !important;
        }
        html.sidebar-collapsed .sidebar-toggle-icon {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="h-full antialiased font-sans text-slate-900 bg-slate-50 overflow-hidden" 
      x-data="{ 
          sidebarOpen: false, 
          toggleSidebar() {
              const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
              localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
          }
      }">
    <div class="h-screen flex overflow-hidden bg-slate-50">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             x-cloak
             @click="sidebarOpen = false" 
             class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-xs lg:hidden transition-opacity"></div>

        <!-- Sidebar Component (No-flicker, Clean SaaS) -->
        <x-sidebar />

        <!-- Hidden Logout Form -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
            <!-- Top Header (Fixed Navbar) -->
            <header class="top-navbar h-16 shrink-0 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 shadow-xs z-20">
                <div class="flex items-center space-x-3">
                    <!-- Mobile Hamburger Toggle -->
                    <button @click="sidebarOpen = true" 
                            type="button"
                            class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Desktop Collapse/Expand Toggle Button -->
                    <button @click="toggleSidebar()" 
                            type="button" 
                            class="hidden lg:flex items-center justify-center p-2 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors"
                            title="Perkecil / Perbesar Sidebar">
                        <svg class="sidebar-toggle-icon w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="text-lg font-semibold text-slate-900 tracking-tight">
                        @yield('header', 'Operasional')
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#e6faf5] text-[#14433b] border border-[#21c9a4]/30">
                        <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-[#21c9a4]"></span>
                        Waktu Bali: {{ now()->format('H:i') }} WITA
                    </span>

                    <a href="{{ route('bookings.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium bg-[#14433b] text-white hover:bg-[#1b584d] transition-colors shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>+ Booking Baru</span>
                    </a>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 bg-slate-50">
                <!-- Flash Messages -->
                @if(session('success'))
                <div class="mb-6 p-4 rounded-lg bg-[#e6faf5] border border-[#21c9a4]/40 text-[#14433b] flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-[#21c9a4] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-[#14433b]/60 hover:text-[#14433b]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- SweetAlert Logout Confirmation (Matching React ConfirmDialog) -->
    <script>
    function confirmLogout() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin keluar dari sistem {{ $appName ?? "ARTDEVATA" }}?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                document.getElementById('logout-form').submit();
            }
        }
    }
    </script>

    @include('partials.sweetalert')
    @stack('scripts')
</body>
</html>
