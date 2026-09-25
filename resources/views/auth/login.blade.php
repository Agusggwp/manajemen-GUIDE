<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ $appName ?? 'ARTDEVATA' }} | {{ $appTagline ?? 'Guide Management' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-100 font-sans text-slate-900" x-data="{
    fillLogin(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
}">
    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            @if(!empty($appLogo) && file_exists(public_path($appLogo)))
                <div class="inline-flex items-center justify-center p-3 rounded-2xl bg-white shadow-sm mb-3 border border-slate-200">
                    <img src="{{ asset($appLogo) }}" alt="{{ $appName ?? 'ARTDEVATA' }}" class="h-14 w-auto max-w-[200px] object-contain">
                </div>
            @else
                <div class="inline-flex w-14 h-14 rounded-2xl bg-[#14433b] text-[#21c9a4] font-extrabold text-2xl items-center justify-center shadow-md mb-3 border border-white/20">
                    {{ $appShortName ?? substr($appName ?? 'A', 0, 1) }}
                </div>
            @endif
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $appName ?? 'ARTDEVATA' }}</h1>
            <p class="text-xs text-slate-500 font-medium tracking-wide uppercase mt-1">{{ $appTagline ?? 'Tour Guide Management System' }}</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-1">Masuk ke Sistem</h2>
            <p class="text-xs text-slate-500 mb-6">Silakan masukkan email dan password akun Anda.</p>

            @if($errors->any())
            <div class="mb-5 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-[#21c9a4] focus:border-transparent transition-all">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-xs font-semibold text-slate-700">Password</label>
                    </div>
                    <input type="password" id="password" name="password" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm focus:outline-hidden focus:ring-2 focus:ring-[#21c9a4] focus:border-transparent transition-all">
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center text-xs text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#14433b] border-slate-300 focus:ring-[#21c9a4]">
                        <span class="ml-2">Ingat saya</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full py-2.5 px-4 rounded-lg bg-[#14433b] hover:bg-[#1b584d] text-white text-sm font-semibold tracking-wide transition-colors shadow-xs">
                    Masuk Sekarang
                </button>
            </form>

            <!-- Registration Link -->
            <div class="mt-4 pt-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-600">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="font-semibold text-[#14433b] hover:text-[#21c9a4] transition-colors ml-1 inline-flex items-center gap-1">
                        Daftar Staff / Guide
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </p>
                <div class="mt-1">
                    <a href="{{ route('verification.notice') }}" class="text-[11px] text-slate-400 hover:text-slate-600 transition-colors">
                        Belum verifikasi email pendaftaran? Cek di sini
                    </a>
                </div>
            </div>

            <!-- Quick Demo Credentials -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2 text-center">Akun Demo (Klik untuk Isi):</div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" @click="fillLogin('admin@example.com', 'password')"
                            class="p-2 rounded-md bg-slate-50 hover:bg-[#e6faf5] border border-slate-200 text-left transition-colors">
                        <div class="font-semibold text-slate-800">Admin</div>
                        <div class="text-[10px] text-slate-500">admin@example.com</div>
                    </button>
                    <button type="button" @click="fillLogin('staff@example.com', 'password')"
                            class="p-2 rounded-md bg-slate-50 hover:bg-[#e6faf5] border border-slate-200 text-left transition-colors">
                        <div class="font-semibold text-slate-800">Staff</div>
                        <div class="text-[10px] text-slate-500">staff@example.com</div>
                    </button>
                    <button type="button" @click="fillLogin('budi@example.com', 'password')"
                            class="p-2 rounded-md bg-slate-50 hover:bg-[#e6faf5] border border-slate-200 text-left transition-colors">
                        <div class="font-semibold text-slate-800">Guide Budi (#1)</div>
                        <div class="text-[10px] text-slate-500">budi@example.com</div>
                    </button>
                    <button type="button" @click="fillLogin('made@example.com', 'password')"
                            class="p-2 rounded-md bg-slate-50 hover:bg-[#e6faf5] border border-slate-200 text-left transition-colors">
                        <div class="font-semibold text-slate-800">Guide Made (#2)</div>
                        <div class="text-[10px] text-slate-500">made@example.com</div>
                    </button>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-400">
            {{ $appFooterText ?? ('© ' . date('Y') . ' ' . ($appName ?? 'ARTDEVATA') . '. Hak cipta dilindungi.') }}
        </div>
    </div>

    @include('partials.sweetalert')
</body>
</html>
