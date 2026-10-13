<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Terverifikasi - {{ $appName ?? 'ARTDEVATA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 bg-[#F4F3FA] font-sans text-[#29283D] relative overflow-hidden">
    <!-- Floating Background Blobs -->
    <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-purple-200/50 blur-3xl pointer-events-none animate-[clayFloat_9s_ease-in-out_infinite]"></div>
    <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-orange-200/40 blur-3xl pointer-events-none animate-[clayFloat_11s_ease-in-out_infinite]"></div>

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
            <!-- Success Icon -->
            <div class="w-16 h-16 rounded-3xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h2 class="text-xl font-black text-[#29283D]">Email Berhasil Diverifikasi!</h2>
            <p class="text-xs text-[#77768C] leading-relaxed">
                Alamat email <strong class="text-[#29283D] font-bold">{{ $user->email }}</strong> telah berhasil diverifikasi oleh sistem.
            </p>

            @if($user->isApproved())
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 text-left">
                    <p class="font-bold mb-1">Akun Anda Sudah Aktif!</p>
                    <p>Akun Anda telah disetujui oleh Administrator. Anda sekarang dapat masuk ke sistem menggunakan email dan password yang didaftarkan.</p>
                </div>
            @else
                <!-- Pending Approval Card -->
                <div class="p-4 rounded-2xl bg-amber-50/80 border border-amber-200 text-xs text-amber-950 text-left space-y-2">
                    <div class="flex items-center gap-2 font-bold text-amber-900">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Menunggu Persetujuan (ACC) Admin
                    </div>
                    <p class="leading-relaxed text-[#77768C]">
                        Demi menjaga keamanan operasional, akun baru dengan peran <strong>{{ $user->role }}</strong> memerlukan peninjauan dan persetujuan dari Administrator.
                    </p>
                    <p class="leading-relaxed text-[11px] text-amber-800 font-medium">
                        Anda akan menerima notifikasi email segera setelah akun Anda disetujui. Silakan hubungi admin operasional jika membutuhkan persetujuan segera.
                    </p>
                </div>
            @endif

            <a href="{{ route('login') }}" 
               class="clay-btn-primary w-full py-3 px-4 text-xs font-bold inline-flex items-center justify-center gap-2">
                <span>Menuju Halaman Masuk</span>
                <svg class="w-4 h-4 text-[#FFD166]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>

        <div class="text-center mt-6 text-xs text-[#77768C]">
            {{ $appFooterText ?? ('© ' . date('Y') . ' ' . ($appName ?? 'ARTDEVATA') . '. Hak cipta dilindungi.') }}
        </div>
    </div>

    @include('partials.sweetalert')
</body>
</html>
