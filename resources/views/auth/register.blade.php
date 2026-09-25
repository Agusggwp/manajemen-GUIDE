<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun Staff & Guide - {{ $appName ?? 'ARTDEVATA' }} | {{ $appTagline ?? 'Guide Management' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-100 font-sans text-slate-900" x-data="{ selectedRole: '{{ old('role', 'STAFF') }}' }">
    <div class="w-full max-w-lg my-8">
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            @if(!empty($appLogo) && file_exists(public_path($appLogo)))
                <div class="inline-flex items-center justify-center p-3 rounded-2xl bg-white shadow-sm mb-3 border border-slate-200">
                    <img src="{{ asset($appLogo) }}" alt="{{ $appName ?? 'ARTDEVATA' }}" class="h-12 w-auto max-w-[180px] object-contain">
                </div>
            @else
                <div class="inline-flex w-12 h-12 rounded-2xl bg-[#14433b] text-[#21c9a4] font-extrabold text-xl items-center justify-center shadow-md mb-3 border border-white/20">
                    {{ $appShortName ?? substr($appName ?? 'A', 0, 1) }}
                </div>
            @endif
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $appName ?? 'ARTDEVATA' }}</h1>
            <p class="text-xs text-slate-500 font-medium tracking-wide uppercase mt-1">Registrasi Akun Staff & Tour Guide</p>
        </div>

        <!-- Register Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-1">Daftar Akun Baru</h2>
            <p class="text-xs text-slate-500 mb-5">Pendaftaran akun operasional untuk staf operasional atau tour guide.</p>

            <!-- Info Alert -->
            <div class="mb-5 p-3.5 rounded-lg bg-emerald-50 border border-emerald-200/80 flex items-start gap-3 text-xs text-emerald-900">
                <svg class="w-5 h-5 text-[#14433b] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="leading-relaxed">
                    <span class="font-semibold">Prosedur Keamanan:</span> Setelah mendaftar, link verifikasi akan dikirimkan ke email Anda. Setelah email terverifikasi, akun harus <strong>di-ACC (disetujui) oleh Administrator</strong> sebelum Anda dapat masuk ke sistem.
                </div>
            </div>

            @if($errors->any())
            <div class="mb-5 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Role Selection (STAFF vs GUIDE) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Peran Akun <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex flex-col p-3 rounded-lg border-2 cursor-pointer transition-all"
                               :class="selectedRole === 'STAFF' ? 'border-[#14433b] bg-emerald-50/50 ring-2 ring-[#21c9a4]/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" name="role" value="STAFF" class="sr-only" x-model="selectedRole">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-xs text-slate-900">Staff Operasional</span>
                                <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                      :class="selectedRole === 'STAFF' ? 'border-[#14433b] bg-[#14433b]' : 'border-slate-300'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedRole === 'STAFF'"></span>
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-500">Kelola booking, tamu, dan pembagian tour guide.</span>
                        </label>

                        <label class="relative flex flex-col p-3 rounded-lg border-2 cursor-pointer transition-all"
                               :class="selectedRole === 'GUIDE' ? 'border-[#14433b] bg-emerald-50/50 ring-2 ring-[#21c9a4]/20' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <input type="radio" name="role" value="GUIDE" class="sr-only" x-model="selectedRole">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-xs text-slate-900">Tour Guide</span>
                                <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                      :class="selectedRole === 'GUIDE' ? 'border-[#14433b] bg-[#14433b]' : 'border-slate-300'">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="selectedRole === 'GUIDE'"></span>
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-500">Menerima antrean tour, check-in harian & portal guide.</span>
                        </label>
                    </div>
                </div>

                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="Contoh: I Wayan Sudarma"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-[#21c9a4] focus:border-transparent transition-all">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email Aktif <span class="text-rose-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           placeholder="nama@email.com"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-[#21c9a4] focus:border-transparent transition-all">
                    <p class="text-[11px] text-slate-500 mt-1">Pastikan email aktif untuk menerima link konfirmasi akun.</p>
                </div>

                <!-- Phone / WhatsApp -->
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp / HP</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                           placeholder="Contoh: 081234567890"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-[#21c9a4] focus:border-transparent transition-all">
                </div>

                <!-- Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Password <span class="text-rose-500">*</span></label>
                        <input type="password" id="password" name="password" required minlength="8"
                               placeholder="Minimal 8 karakter"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-[#21c9a4] focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">Ulangi Password <span class="text-rose-500">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                               placeholder="Konfirmasi password"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-[#21c9a4] focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-2.5 px-4 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-sm font-semibold tracking-wide transition-colors shadow-xs flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Daftar & Kirim Link Verifikasi
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-600">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}" class="font-semibold text-[#14433b] hover:text-[#21c9a4] transition-colors ml-1">
                        Masuk ke Sistem
                    </a>
                </p>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-400">
            {{ $appFooterText ?? ('© ' . date('Y') . ' ' . ($appName ?? 'ARTDEVATA') . '. Hak cipta dilindungi.') }}
        </div>
    </div>

    @include('partials.sweetalert')
</body>
</html>
