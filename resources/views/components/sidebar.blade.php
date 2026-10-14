<!-- Sidebar Component (Clean Light Modern Theme, Pure Static CSS Zero-Flicker) -->
<aside id="main-sidebar"
       :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'"
       class="sidebar-container fixed inset-y-0 left-0 z-50 bg-white border-r border-slate-200 flex flex-col -translate-x-full lg:translate-x-0 lg:static lg:inset-auto lg:h-full lg:shrink-0 select-none">
    
    <div class="flex flex-col h-full overflow-hidden">
        <!-- Logo Brand Header -->
        <div class="sidebar-header h-16 border-b border-slate-200 flex items-center justify-between shrink-0 px-4">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden">
                <!-- Logo Mark (Always visible) -->
                @if(!empty($appLogo) && file_exists(public_path($appLogo)))
                    <img src="{{ asset($appLogo) }}" alt="{{ $appName ?? 'ARTDEVATA' }}" 
                         class="h-9 w-9 object-contain shrink-0 cursor-pointer hover:opacity-90 mx-auto"
                         @click="document.documentElement.classList.contains('sidebar-collapsed') ? toggleSidebar() : null"
                         title="{{ $appName ?? 'ARTDEVATA' }}">
                @else
                    <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-[#6C63FF] to-[#5850E6] text-[#FFD166] font-black flex items-center justify-center text-sm shadow-[0_4px_12px_rgba(108,99,255,0.35)] shrink-0 cursor-pointer hover:opacity-95 mx-auto"
                         @click="document.documentElement.classList.contains('sidebar-collapsed') ? toggleSidebar() : null"
                         title="{{ $appName ?? 'ARTDEVATA' }}">
                        {{ $appShortName ?? substr($appName ?? 'A', 0, 2) }}
                    </div>
                @endif

                <!-- Brand Text (Hidden when collapsed on desktop via CSS) -->
                <div class="sidebar-text truncate">
                    <h1 class="font-bold text-slate-900 tracking-tight text-base leading-tight truncate">
                        {{ $appName ?? 'ARTDEVATA' }}
                    </h1>
                    <p class="text-[11px] text-slate-400 font-medium tracking-wide uppercase truncate">
                        {{ $appTagline ?? 'Portal Manajemen Guide' }}
                    </p>
                </div>
            </a>

            <!-- Mobile Close Button -->
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-700 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Navigation Items -->
        <nav class="sidebar-nav flex-1 overflow-y-auto overflow-x-hidden no-scrollbar p-4 space-y-6"
             style="scrollbar-width: none; -ms-overflow-style: none;">

            <!-- GROUP 1: UTAMA -->
            <div>
                <p class="sidebar-group-title sidebar-text text-[10px] font-bold tracking-wider text-slate-400 uppercase mb-2 px-2">
                    UTAMA
                </p>
                <div class="sidebar-group-divider hidden h-px bg-slate-100 my-2 mx-1"></div>

                <div class="space-y-1">
                    <!-- Dashboard -->
                    @php $active = request()->routeIs('dashboard'); @endphp
                    <a href="{{ route('dashboard') }}" 
                       @click="sidebarOpen = false"
                       title="Dashboard Utama"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Dashboard Utama
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>

                    <!-- Papan Antrean Guide -->
                    @php $active = request()->routeIs('queue.board'); @endphp
                    <a href="{{ route('queue.board') }}" 
                       @click="sidebarOpen = false"
                       title="Papan Antrean Guide"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Papan Antrean Guide
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>
                </div>
            </div>

            <!-- GROUP 2: OPERASIONAL -->
            <div>
                <p class="sidebar-group-title sidebar-text text-[10px] font-bold tracking-wider text-slate-400 uppercase mb-2 px-2">
                    OPERASIONAL
                </p>
                <div class="sidebar-group-divider hidden h-px bg-slate-100 my-2 mx-1"></div>

                <div class="space-y-1">
                    <!-- Tour Langsung -->
                    @php $active = request()->routeIs('direct-tours.*'); @endphp
                    <a href="{{ route('direct-tours.create') }}" 
                       @click="sidebarOpen = false"
                       title="Tour Langsung (Walk-in)"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-2.5 min-w-0">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-amber-400' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap truncate">
                                Tour Langsung
                            </span>
                        </div>
                        <div class="sidebar-text flex items-center gap-1.5 shrink-0 ml-1.5">
                            <span class="inline-flex items-center px-1.5 py-0.5 text-[9px] font-bold uppercase rounded bg-amber-100 text-amber-800 border border-amber-200 whitespace-nowrap shrink-0 leading-none">
                                Walk-in
                            </span>
                            @if($active)
                                <svg class="h-3.5 w-3.5 opacity-70 shrink-0 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            @endif
                        </div>
                    </a>

                    <!-- Pemesanan (Booking) -->
                    @php $active = request()->routeIs('bookings.*'); @endphp
                    <a href="{{ route('bookings.index') }}" 
                       @click="sidebarOpen = false"
                       title="Pemesanan (Booking)"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Pemesanan (Booking)
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>

                    <!-- Absensi Guide -->
                    @php $active = request()->routeIs('attendance.*'); @endphp
                    <a href="{{ route('attendance.index') }}" 
                       @click="sidebarOpen = false"
                       title="Absensi Guide"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Absensi Guide
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>

                    <!-- Data Guide -->
                    @php $active = request()->routeIs('guides.*'); @endphp
                    <a href="{{ route('guides.index') }}" 
                       @click="sidebarOpen = false"
                       title="Data Guide"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Data Guide
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>

                    <!-- Paket Wisata & Tarif -->
                    @php $active = request()->routeIs('packages.*'); @endphp
                    <a href="{{ route('packages.index') }}" 
                       @click="sidebarOpen = false"
                       title="Paket Wisata & Tarif"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Paket Wisata & Tarif
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>
                </div>
            </div>

            <!-- GROUP 3: KEUANGAN (Admin Only) -->
            @if(auth()->user()->isAdmin())
            <div>
                <p class="sidebar-group-title sidebar-text text-[10px] font-bold tracking-wider text-slate-400 uppercase mb-2 px-2">
                    KEUANGAN
                </p>
                <div class="sidebar-group-divider hidden h-px bg-slate-100 my-2 mx-1"></div>

                <div class="space-y-1">
                    <!-- Payroll Guide -->
                    @php $active = request()->routeIs('payroll.index') || request()->routeIs('payroll.show'); @endphp
                    <a href="{{ route('payroll.index') }}" 
                       @click="sidebarOpen = false"
                       title="Penggajian Guide (Payroll)"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Penggajian Guide
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>

                    <!-- Laporan Gaji per Guide -->
                    @php $active = request()->routeIs('reports.guide-payroll'); @endphp
                    <a href="{{ route('reports.guide-payroll') }}" 
                       @click="sidebarOpen = false"
                       title="Laporan Gaji per Guide"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Laporan Gaji Guide
                            </span>
                        </div>
                        <span class="sidebar-text px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $active ? 'bg-white/20 text-white' : 'bg-[#6C63FF]/10 text-[#6C63FF]' }}">
                            Rekap
                        </span>
                    </a>

                    <!-- Staff Payroll (Gaji Staff) -->
                    @php $active = request()->routeIs('staff-payroll.*'); @endphp
                    <a href="{{ route('staff-payroll.index') }}" 
                       @click="sidebarOpen = false"
                       title="Penggajian Staff (Staff Payroll)"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Gaji Staff
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>

                    <!-- Riwayat Pembayaran -->
                    @php $active = request()->routeIs('payroll.payments'); @endphp
                    <a href="{{ route('payroll.payments') }}" 
                       @click="sidebarOpen = false"
                       title="Riwayat Pembayaran"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Riwayat Pembayaran
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>

                    <!-- Biaya Operasional -->
                    @php $active = request()->routeIs('expenses.*'); @endphp
                    <a href="{{ route('expenses.index') }}" 
                       @click="sidebarOpen = false"
                       title="Biaya Operasional"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Biaya Operasional
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>
                </div>
            </div>
            @endif

            @if(auth()->user()->isStaff())
            <!-- STAFF SPECIFIC: KEPEGAWAIAN -->
            <div>
                <p class="sidebar-group-title sidebar-text text-[10px] font-bold tracking-wider text-slate-400 uppercase mb-2 px-2">
                    KEPEGAWAIAN
                </p>
                <div class="sidebar-group-divider hidden h-px bg-slate-100 my-2 mx-1"></div>

                <div class="space-y-1">
                    @php $active = request()->routeIs('staff.my-salary'); @endphp
                    <a href="{{ route('staff.my-salary') }}" 
                       @click="sidebarOpen = false"
                       title="Slip Gaji Saya"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Slip Gaji Saya
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>
                </div>
            </div>
            @endif

            <!-- GROUP 4: LAPORAN & SISTEM -->
            <div>
                <p class="sidebar-group-title sidebar-text text-[10px] font-bold tracking-wider text-slate-400 uppercase mb-2 px-2">
                    LAPORAN & SISTEM
                </p>
                <div class="sidebar-group-divider hidden h-px bg-slate-100 my-2 mx-1"></div>

                <div class="space-y-1">
                    <!-- Laporan Operasional -->
                    @php $active = request()->routeIs('reports.*') && !request()->routeIs('reports.guide-payroll'); @endphp
                    <a href="{{ route('reports.revenue') }}" 
                       @click="sidebarOpen = false"
                       title="Laporan Operasional"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Laporan Operasional
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>

                    <!-- Riwayat Antrean -->
                    @php $active = request()->routeIs('queue.history'); @endphp
                    <a href="{{ route('queue.history') }}" 
                       @click="sidebarOpen = false"
                       title="Riwayat Antrean"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Riwayat Antrean
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>

                    @if(auth()->user()->isAdmin())
                    <!-- Pengaturan Sistem -->
                    @php $active = request()->routeIs('settings.*'); @endphp
                    <a href="{{ route('settings.index') }}" 
                       @click="sidebarOpen = false"
                       title="Pengaturan Sistem"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Pengaturan Sistem
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>

                    <!-- Persetujuan User (ACC) -->
                    @php 
                        $active = request()->routeIs('admin.user-approvals.*'); 
                        $pendingApprovalsCount = \App\Models\User::where('approval_status', 'PENDING')->count();
                    @endphp
                    <a href="{{ route('admin.user-approvals.index') }}" 
                       @click="sidebarOpen = false"
                       title="Persetujuan User"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Persetujuan User
                            </span>
                        </div>
                        <div class="sidebar-text flex items-center gap-1.5 shrink-0 ml-1.5">
                            @if($pendingApprovalsCount > 0)
                                <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-400 text-amber-950 whitespace-nowrap shrink-0 leading-none">
                                    {{ $pendingApprovalsCount }}
                                </span>
                            @endif
                            @if($active)
                                <svg class="h-3.5 w-3.5 opacity-70 shrink-0 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            @endif
                        </div>
                    </a>

                    <!-- Log Audit -->
                    @php $active = request()->routeIs('audit.*'); @endphp
                    <a href="{{ route('audit.index') }}" 
                       @click="sidebarOpen = false"
                       title="Log Audit Sistem"
                       class="sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl group relative cursor-pointer transition-all duration-150 {{ $active ? 'bg-[#6C63FF] text-white shadow-md shadow-[#6C63FF]/30 font-semibold hover:bg-[#5850E6] hover:shadow-lg hover:shadow-[#6C63FF]/45 active:scale-[0.97]' : 'text-[#29283D] hover:text-[#6C63FF] hover:bg-[#ECEBFC] active:scale-[0.97]' }}">
                        <div class="sidebar-link-content flex items-center space-x-3">
                            <svg class="h-4 w-4 shrink-0 {{ $active ? 'text-white' : 'text-[#77768C] group-hover:text-[#6C63FF]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="sidebar-text text-sm font-medium whitespace-nowrap">
                                Log Audit
                            </span>
                        </div>
                        @if($active)
                            <svg class="sidebar-text h-3.5 w-3.5 opacity-80 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </a>
                    @endif


                </div>
            </div>
        </nav>

        <!-- User Profile & Logout in Sidebar Footer -->
        <div class="sidebar-footer border-t border-[#E8E6F0] bg-[#F4F3FA]/70 shrink-0 p-3">
            <!-- Compact Profile for Desktop Collapsed State -->
            <div class="sidebar-footer-collapsed flex-col items-center space-y-2.5">
                <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-[#6C63FF] to-[#5850E6] text-white font-bold text-xs flex items-center justify-center ring-2 ring-white shadow-sm cursor-pointer"
                     title="{{ auth()->user()->name }} ({{ auth()->user()->email }})">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <button type="button" 
                        onclick="confirmLogout()" 
                        title="Keluar (Logout)"
                        class="w-9 h-9 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 flex items-center justify-center cursor-pointer shadow-2xs transition-colors active:scale-95">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </div>

            <!-- Full Profile on Mobile or Expanded Desktop -->
            <div class="sidebar-footer-expanded flex-col gap-2.5">
                <div class="flex items-center space-x-3 overflow-hidden min-w-0">
                    <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-[#6C63FF] to-[#5850E6] text-white font-bold text-xs flex items-center justify-center shrink-0 ring-2 ring-white shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="truncate min-w-0">
                        <p class="text-xs font-bold text-[#29283D] truncate">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-[10px] text-[#77768C] truncate flex items-center gap-1.5">
                            <span>{{ auth()->user()->email }}</span>
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold uppercase bg-[#ECEBFC] text-[#5850E6]">
                                {{ auth()->user()->role }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Destructive Logout Button -->
                <button type="button" 
                        onclick="confirmLogout()"
                        title="Keluar dari Sistem"
                        class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold cursor-pointer transition shadow-2xs">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Keluar</span>
                </button>
            </div>
        </div>
    </div>
</aside>
