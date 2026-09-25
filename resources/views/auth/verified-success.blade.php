<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Terverifikasi - {{ $appName ?? 'ARTDEVATA' }}</title>
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
            <!-- Success Icon -->
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 ring-8 ring-emerald-50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h2 class="text-xl font-bold text-slate-900 mb-2">Email Berhasil Diverifikasi!</h2>
            <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                Alamat email <strong class="text-slate-900 font-semibold">{{ $user->email }}</strong> telah berhasil diverifikasi oleh sistem.
            </p>

            @if($user->isApproved())
                <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 text-left mb-6">
                    <p class="font-semibold mb-1">Akun Anda Sudah Aktif!</p>
                    <p>Akun Anda telah disetujui oleh Administrator. Anda sekarang dapat masuk ke sistem menggunakan email dan password yang didaftarkan.</p>
                </div>
            @else
                <!-- Pending Approval Card -->
                <div class="p-4 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-900 text-left mb-6 space-y-2">
                    <div class="flex items-center gap-2 font-semibold text-amber-950">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Menunggu Persetujuan (ACC) Admin
                    </div>
                    <p class="leading-relaxed">
                        Demi menjaga keamanan operasional, akun baru dengan peran <strong>{{ $user->role }}</strong> memerlukan peninjauan dan persetujuan dari Administrator.
                    </p>
                    <p class="leading-relaxed text-[11px] text-amber-800">
                        Anda akan menerima notifikasi email segera setelah akun Anda disetujui. Silakan hubungi admin operasional jika membutuhkan persetujuan segera.
                    </p>
                </div>
            @endif

            <a href="{{ route('login') }}" 
               class="w-full py-2.5 px-4 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-xs font-semibold tracking-wide transition-colors shadow-xs inline-flex items-center justify-center gap-2">
                Menuju Halaman Masuk
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>

        <div class="text-center mt-6 text-xs text-slate-400">
            {{ $appFooterText ?? ('© ' . date('Y') . ' ' . ($appName ?? 'ARTDEVATA') . '. Hak cipta dilindungi.') }}
        </div>
    </div>

    @include('partials.sweetalert')
</body>
</html>
