<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F4F3FA]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun Staff & Guide - {{ $appName ?? 'ARTDEVATA' }} | {{ $appTagline ?? 'Guide Management' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex items-center justify-center p-4 bg-[#F4F3FA] font-sans text-[#29283D] relative overflow-x-hidden selection:bg-[#6C63FF]/20 selection:text-[#6C63FF]" x-data="{ selectedRole: '{{ old('role', 'STAFF') }}' }">
    <!-- Organic Clay Background Blobs -->
    <div class="fixed -top-24 -left-20 w-96 h-96 rounded-full bg-gradient-to-br from-[#6C63FF]/15 to-[#72D6C9]/10 blur-3xl pointer-events-none -z-10 animate-[clayFloat_10s_ease-in-out_infinite]"></div>
    <div class="fixed -bottom-28 -right-20 w-96 h-96 rounded-full bg-gradient-to-tr from-[#FF8A65]/15 to-[#FFD166]/15 blur-3xl pointer-events-none -z-10 animate-[clayFloat_12s_ease-in-out_infinite_reverse]"></div>

    <div class="w-full max-w-lg my-8 relative z-10">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            @if(!empty($appLogo) && file_exists(public_path($appLogo)))
                <div class="inline-flex items-center justify-center p-3 rounded-2xl bg-white clay-card mb-3">
                    <img src="{{ asset($appLogo) }}" alt="{{ $appName ?? 'ARTDEVATA' }}" class="h-12 w-auto max-w-[180px] object-contain">
                </div>
            @else
                <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-br from-[#6C63FF] to-[#554cd8] text-white font-extrabold text-2xl items-center justify-center shadow-lg shadow-[#6C63FF]/25 mb-3 border border-white/40 ring-4 ring-[#6C63FF]/10">
                    {{ $appShortName ?? substr($appName ?? 'A', 0, 1) }}
                </div>
            @endif
            <h1 class="text-2xl font-extrabold tracking-tight text-[#29283D] font-display">{{ $appName ?? 'ARTDEVATA' }}</h1>
            <p class="text-xs text-[#77768C] font-semibold tracking-wider uppercase mt-1">Registrasi Akun Staff & Tour Guide</p>
        </div>

        <!-- Register Card -->
        <div class="clay-card p-6 sm:p-8 bg-white/95 backdrop-blur-md">
            <h2 class="text-xl font-bold text-[#29283D] font-display mb-1">Daftar Akun Baru</h2>
            <p class="text-xs text-[#77768C] mb-5">Pendaftaran akun operasional untuk staf operasional atau tour guide.</p>

            <!-- Info Alert -->
            <div class="mb-5 p-4 rounded-2xl bg-[#6C63FF]/8 border border-[#6C63FF]/20 flex items-start gap-3 text-xs text-[#29283D]">
                <div class="w-7 h-7 rounded-xl bg-[#6C63FF]/15 text-[#6C63FF] flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="leading-relaxed">
                    <span class="font-bold text-[#6C63FF]">Prosedur Keamanan:</span> Setelah mendaftar, link verifikasi akan dikirimkan ke email Anda. Setelah email terverifikasi, akun harus <strong>di-ACC (disetujui) oleh Administrator</strong> sebelum Anda dapat masuk ke sistem.
                </div>
            </div>

            @if($errors->any())
            <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs shadow-inner">
                @foreach ($errors->all() as $error)
                    <p class="font-medium">• {{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Role Selection (STAFF vs GUIDE) -->
                <div>
                    <label class="block text-xs font-bold text-[#29283D] mb-2 uppercase tracking-wider">Pilih Peran Akun <span class="text-[#FF8A65]">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex flex-col p-3.5 rounded-2xl border cursor-pointer transition-all duration-200"
                               :class="selectedRole === 'STAFF' ? 'border-[#6C63FF] bg-[#6C63FF]/5 shadow-[inset_0_2px_4px_rgba(108,99,255,0.06),0_8px_16px_rgba(108,99,255,0.12)] ring-2 ring-[#6C63FF]/20' : 'border-[#E8E6F0] bg-white hover:border-[#6C63FF]/40 shadow-sm'">
                            <input type="radio" name="role" value="STAFF" class="sr-only" x-model="selectedRole">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-xs text-[#29283D]">Staff Operasional</span>
                                <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center transition-colors"
                                      :class="selectedRole === 'STAFF' ? 'border-[#6C63FF] bg-[#6C63FF]' : 'border-slate-300'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedRole === 'STAFF'"></span>
                                </span>
                            </div>
                            <span class="text-[11px] text-[#77768C] leading-tight">Kelola booking, tamu, dan pembagian tour guide.</span>
                        </label>

                        <label class="relative flex flex-col p-3.5 rounded-2xl border cursor-pointer transition-all duration-200"
                               :class="selectedRole === 'GUIDE' ? 'border-[#6C63FF] bg-[#6C63FF]/5 shadow-[inset_0_2px_4px_rgba(108,99,255,0.06),0_8px_16px_rgba(108,99,255,0.12)] ring-2 ring-[#6C63FF]/20' : 'border-[#E8E6F0] bg-white hover:border-[#6C63FF]/40 shadow-sm'">
                            <input type="radio" name="role" value="GUIDE" class="sr-only" x-model="selectedRole">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-xs text-[#29283D]">Tour Guide</span>
                                <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center transition-colors"
                                      :class="selectedRole === 'GUIDE' ? 'border-[#6C63FF] bg-[#6C63FF]' : 'border-slate-300'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedRole === 'GUIDE'"></span>
                                </span>
                            </div>
                            <span class="text-[11px] text-[#77768C] leading-tight">Menerima antrean tour, check-in harian & portal guide.</span>
                        </label>
                    </div>
                </div>

                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-xs font-bold text-[#29283D] mb-1.5">Nama Lengkap <span class="text-[#FF8A65]">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="Contoh: I Wayan Sudarma"
                           class="clay-input text-sm">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold text-[#29283D] mb-1.5">Alamat Email Aktif <span class="text-[#FF8A65]">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           placeholder="nama@email.com"
                           class="clay-input text-sm">
                    <p class="text-[11px] text-[#77768C] mt-1.5">Pastikan email aktif untuk menerima link konfirmasi akun.</p>
                </div>

                <!-- Phone / WhatsApp -->
                <div>
                    <label for="phone" class="block text-xs font-bold text-[#29283D] mb-1.5">Nomor WhatsApp / HP</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                           placeholder="Contoh: 081234567890"
                           class="clay-input text-sm">
                </div>

                <!-- Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block text-xs font-bold text-[#29283D] mb-1.5">Password <span class="text-[#FF8A65]">*</span></label>
                        <input type="password" id="password" name="password" required minlength="8"
                               placeholder="Minimal 8 karakter"
                               class="clay-input text-sm">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-[#29283D] mb-1.5">Ulangi Password <span class="text-[#FF8A65]">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                               placeholder="Konfirmasi password"
                               class="clay-input text-sm">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="clay-btn clay-btn-primary w-full py-3.5 px-4 text-sm font-bold flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Daftar & Kirim Link Verifikasi
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-[#E8E6F0] text-center">
                <p class="text-xs text-[#77768C] font-medium">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}" class="font-bold text-[#6C63FF] hover:text-[#554cd8] transition-colors ml-1 underline decoration-2 decoration-[#6C63FF]/30">
                        Masuk ke Sistem
                    </a>
                </p>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-[#77768C]">
            {{ $appFooterText ?? ('© ' . date('Y') . ' ' . ($appName ?? 'ARTDEVATA') . '. Hak cipta dilindungi.') }}
        </div>
    </div>

    @include('partials.sweetalert')
</body>
</html>
