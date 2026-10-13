<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F4F3FA]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ $appName ?? 'ARTDEVATA' }} | {{ $appTagline ?? 'Guide Management' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex items-center justify-center p-4 bg-[#F4F3FA] font-sans text-[#29283D] relative overflow-x-hidden" x-data="{
    fillLogin(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
}">
    <!-- Soft Decorative Organic Clay Blobs -->
    <div class="fixed -top-24 -left-24 w-96 h-96 rounded-full bg-[#6C63FF]/12 blur-3xl pointer-events-none animate-clay-float"></div>
    <div class="fixed -bottom-24 -right-24 w-96 h-96 rounded-full bg-[#FF8A65]/12 blur-3xl pointer-events-none"></div>
    <div class="fixed top-1/3 -right-20 w-64 h-64 rounded-full bg-[#FFD166]/15 blur-2xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10 py-8">
        <!-- Logo & Header -->
        <div class="text-center mb-7">
            @if(!empty($appLogo) && file_exists(public_path($appLogo)))
                <div class="inline-flex items-center justify-center p-3.5 rounded-3xl bg-white shadow-[6px_8px_20px_rgba(55,48,100,0.08)] mb-3 border border-white">
                    <img src="{{ asset($appLogo) }}" alt="{{ $appName ?? 'ARTDEVATA' }}" class="h-14 w-auto max-w-[200px] object-contain">
                </div>
            @else
                <div class="inline-flex w-16 h-16 rounded-3xl bg-gradient-to-br from-[#6C63FF] to-[#5850E6] text-[#FFD166] font-black text-2xl items-center justify-center shadow-[6px_10px_24px_rgba(108,99,255,0.35),inset_0_2px_2px_rgba(255,255,255,0.5)] mb-3 border border-white/25">
                    {{ $appShortName ?? substr($appName ?? 'A', 0, 1) }}
                </div>
            @endif
            <h1 class="text-2xl font-black tracking-tight text-[#29283D]">{{ $appName ?? 'ARTDEVATA' }}</h1>
            <p class="text-xs text-[#77768C] font-bold tracking-wider uppercase mt-1">{{ $appTagline ?? 'Tour Guide Management System' }}</p>
        </div>

        <!-- Login Clay Card -->
        <div class="clay-card p-7 sm:p-9 rounded-[28px]">
            <h2 class="text-lg font-bold text-[#29283D] mb-1">Masuk ke Sistem</h2>
            <p class="text-xs text-[#77768C] mb-6">Silakan masukkan email dan password akun Anda.</p>

            @if($errors->any())
            <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium shadow-xs">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold text-[#29283D] mb-1.5">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="nama@email.com"
                           class="clay-input text-sm">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-[#29283D]">Password</label>
                    </div>
                    <input type="password" id="password" name="password" required
                           placeholder="••••••••"
                           class="clay-input text-sm">
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center text-xs text-[#77768C] cursor-pointer font-medium">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#6C63FF] border-[#E8E6F0] focus:ring-[#6C63FF]">
                        <span class="ml-2">Ingat saya</span>
                    </label>
                </div>

                <button type="submit" 
                        class="clay-btn clay-btn-primary w-full py-3 px-4 text-sm font-bold tracking-wide mt-2">
                    Masuk Sekarang
                </button>
            </form>

            <!-- Registration Link -->
            <div class="mt-5 pt-4 border-t border-[#E8E6F0] text-center">
                <p class="text-xs text-[#77768C]">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-bold text-[#6C63FF] hover:text-[#5850E6] transition-colors ml-1 inline-flex items-center gap-1">
                        Daftar Staff / Guide
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </p>
                <div class="mt-1.5">
                    <a href="{{ route('verification.notice') }}" class="text-[11px] text-[#77768C] hover:text-[#6C63FF] transition-colors">
                        Belum verifikasi email pendaftaran? Cek di sini
                    </a>
                </div>
            </div>

            <!-- Quick Demo Credentials (Clay Cards) -->
            <div class="mt-6 pt-5 border-t border-[#E8E6F0]">
                <div class="text-[11px] font-bold text-[#77768C] uppercase tracking-wider mb-2.5 text-center">Akun Demo (Klik untuk Isi):</div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" @click="fillLogin('admin@example.com', 'password')"
                            class="clay-card-sm p-2.5 text-left cursor-pointer hover:border-[#6C63FF]/50 transition-transform hover:-translate-y-0.5">
                        <div class="font-bold text-[#29283D] flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-[#6C63FF]"></span>
                            <span>Admin</span>
                        </div>
                        <div class="text-[10px] text-[#77768C] truncate mt-0.5">admin@example.com</div>
                    </button>
                    <button type="button" @click="fillLogin('staff@example.com', 'password')"
                            class="clay-card-sm p-2.5 text-left cursor-pointer hover:border-[#FF8A65]/50 transition-transform hover:-translate-y-0.5">
                        <div class="font-bold text-[#29283D] flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-[#FF8A65]"></span>
                            <span>Staff</span>
                        </div>
                        <div class="text-[10px] text-[#77768C] truncate mt-0.5">staff@example.com</div>
                    </button>
                    <button type="button" @click="fillLogin('budi@example.com', 'password')"
                            class="clay-card-sm p-2.5 text-left cursor-pointer hover:border-[#72D6C9]/50 transition-transform hover:-translate-y-0.5">
                        <div class="font-bold text-[#29283D] flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-[#72D6C9]"></span>
                            <span>Guide Budi (#1)</span>
                        </div>
                        <div class="text-[10px] text-[#77768C] truncate mt-0.5">budi@example.com</div>
                    </button>
                    <button type="button" @click="fillLogin('made@example.com', 'password')"
                            class="clay-card-sm p-2.5 text-left cursor-pointer hover:border-[#FFD166]/50 transition-transform hover:-translate-y-0.5">
                        <div class="font-bold text-[#29283D] flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-[#FFD166]"></span>
                            <span>Guide Made (#2)</span>
                        </div>
                        <div class="text-[10px] text-[#77768C] truncate mt-0.5">made@example.com</div>
                    </button>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-[#77768C]">
            {{ $appFooterText ?? ('© ' . date('Y') . ' ' . ($appName ?? 'ARTDEVATA') . '. Hak cipta dilindungi.') }}
        </div>
    </div>

    @include('partials.sweetalert')
</body>
</html>
