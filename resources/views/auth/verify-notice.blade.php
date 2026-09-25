<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - {{ $appName ?? 'ARTDEVATA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-100 font-sans text-slate-900">
    <div class="w-full max-w-md">
        <!-- Logo -->
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
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-6 sm:p-8 text-center">
            <!-- Icon Mail -->
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-[#14433b] flex items-center justify-center mx-auto mb-4 ring-8 ring-emerald-50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>

            <h2 class="text-xl font-bold text-slate-900 mb-2">Cek Email Anda</h2>
            <p class="text-sm text-slate-600 mb-5 leading-relaxed">
                Kami telah mengirimkan tautan verifikasi ke:
                <br>
                <strong class="text-slate-900 font-semibold">{{ $email ?? session('email', 'email Anda') }}</strong>
            </p>

            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600 text-left mb-6 space-y-2">
                <div class="flex items-start gap-2">
                    <span class="inline-flex w-5 h-5 rounded-full bg-[#14433b] text-white text-[11px] items-center justify-center shrink-0 font-bold">1</span>
                    <span>Buka kotak masuk (inbox) atau folder spam pada email Anda.</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="inline-flex w-5 h-5 rounded-full bg-[#14433b] text-white text-[11px] items-center justify-center shrink-0 font-bold">2</span>
                    <span>Klik tombol <strong>"Verifikasi Alamat Email"</strong>.</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="inline-flex w-5 h-5 rounded-full bg-[#14433b] text-white text-[11px] items-center justify-center shrink-0 font-bold">3</span>
                    <span>Setelah terverifikasi, akun Anda akan masuk ke antrean persetujuan (ACC) Administrator.</span>
                </div>
            </div>

            <!-- Resend Form -->
            <form action="{{ route('verification.send') }}" method="POST" class="mb-4">
                @csrf
                <input type="hidden" name="email" value="{{ $email ?? session('email') }}">
                <button type="submit" 
                        class="w-full py-2.5 px-4 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-semibold tracking-wide transition-colors shadow-xs flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Kirim Ulang Link Verifikasi
                </button>
            </form>

            <a href="{{ route('login') }}" class="text-xs text-slate-500 hover:text-slate-800 transition-colors inline-flex items-center gap-1 font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Halaman Masuk
            </a>
        </div>

        <div class="text-center mt-6 text-xs text-slate-400">
            {{ $appFooterText ?? ('© ' . date('Y') . ' ' . ($appName ?? 'ARTDEVATA') . '. Hak cipta dilindungi.') }}
        </div>
    </div>

    @include('partials.sweetalert')
</body>
</html>
