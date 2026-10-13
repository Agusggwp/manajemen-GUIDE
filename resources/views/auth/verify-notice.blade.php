<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - {{ $appName ?? 'ARTDEVATA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 bg-[#F4F3FA] font-sans text-[#29283D] relative overflow-hidden">
    <!-- Floating Background Blobs -->
    <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-purple-200/50 blur-3xl pointer-events-none animate-[clayFloat_9s_ease-in-out_infinite]"></div>
    <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-coral-200/40 blur-3xl pointer-events-none animate-[clayFloat_11s_ease-in-out_infinite]"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo -->
        <div class="text-center mb-6">
            @if(!empty($appLogo) && file_exists(public_path($appLogo)))
                <div class="inline-flex items-center justify-center p-3 rounded-2xl bg-white shadow-md mb-3 border border-[#E8E6F0]">
                    <img src="{{ asset($appLogo) }}" alt="{{ $appName ?? 'ARTDEVATA' }}" class="h-12 w-auto max-w-[180px] object-contain">
                </div>
            @else
                <div class="inline-flex w-14 h-14 rounded-2xl bg-[#6C63FF] text-[#FFD166] font-black text-2xl items-center justify-center shadow-md mb-3 border border-white/40">
                    {{ $appShortName ?? substr($appName ?? 'A', 0, 1) }}
                </div>
            @endif
            <h1 class="text-2xl font-black tracking-tight text-[#29283D]">{{ $appName ?? 'ARTDEVATA' }}</h1>
        </div>

        <div class="clay-card p-6 sm:p-8 text-center space-y-4">
            <!-- Icon Mail -->
            <div class="w-16 h-16 rounded-3xl bg-purple-100 text-[#6C63FF] flex items-center justify-center mx-auto shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>

            <h2 class="text-xl font-black text-[#29283D]">Cek Email Anda</h2>
            <p class="text-xs text-[#77768C] leading-relaxed">
                Kami telah mengirimkan tautan verifikasi ke:
                <br>
                <strong class="text-[#29283D] font-bold">{{ $email ?? session('email', 'email Anda') }}</strong>
            </p>

            <div class="p-4 rounded-2xl bg-[#F4F3FA] border border-[#E8E6F0] text-xs text-[#77768C] text-left space-y-2.5">
                <div class="flex items-start gap-2.5">
                    <span class="inline-flex w-5 h-5 rounded-full bg-[#6C63FF] text-white text-[11px] items-center justify-center shrink-0 font-black shadow-2xs">1</span>
                    <span>Buka kotak masuk (inbox) atau folder spam pada email Anda.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="inline-flex w-5 h-5 rounded-full bg-[#6C63FF] text-white text-[11px] items-center justify-center shrink-0 font-black shadow-2xs">2</span>
                    <span>Klik tombol <strong>"Verifikasi Alamat Email"</strong>.</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <span class="inline-flex w-5 h-5 rounded-full bg-[#6C63FF] text-white text-[11px] items-center justify-center shrink-0 font-black shadow-2xs">3</span>
                    <span>Setelah terverifikasi, akun Anda akan masuk ke antrean persetujuan (ACC) Administrator.</span>
                </div>
            </div>

            <!-- Resend Form -->
            <form action="{{ route('verification.send') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ $email ?? session('email') }}">
                <button type="submit" 
                        class="clay-btn-primary w-full py-3 px-4 text-xs font-bold inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-[#FFD166]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Kirim Ulang Link Verifikasi</span>
                </button>
            </form>

            <a href="{{ route('login') }}" class="text-xs text-[#77768C] hover:text-[#6C63FF] transition-colors inline-flex items-center gap-1 font-bold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Halaman Masuk
            </a>
        </div>

        <div class="text-center mt-6 text-xs text-[#77768C]">
            {{ $appFooterText ?? ('© ' . date('Y') . ' ' . ($appName ?? 'ARTDEVATA') . '. Hak cipta dilindungi.') }}
        </div>
    </div>

    @include('partials.sweetalert')
</body>
</html>
