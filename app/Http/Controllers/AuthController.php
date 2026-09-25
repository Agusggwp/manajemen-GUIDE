<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isGuide() 
                ? redirect()->route('guide.dashboard') 
                : redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // 1. Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                $msg = 'Email Anda belum diverifikasi. Silakan klik link verifikasi yang telah kami kirimkan ke email Anda.';
                return back()->withErrors(['email' => $msg])
                    ->with('error', $msg)
                    ->with('unverified_email', $user->email)
                    ->onlyInput('email');
            }

            // 2. Check if approved by Administrator
            if ($user->approval_status === 'PENDING') {
                Auth::logout();
                $msg = 'Pendaftaran akun Anda telah diverifikasi namun masih menunggu persetujuan (ACC) dari Administrator.';
                return back()->withErrors(['email' => $msg])
                    ->with('error', $msg)
                    ->onlyInput('email');
            }

            if ($user->approval_status === 'REJECTED') {
                Auth::logout();
                $reason = $user->rejection_reason ? ' (' . $user->rejection_reason . ')' : '';
                $msg = 'Pendaftaran akun Anda ditolak oleh Administrator' . $reason . '.';
                return back()->withErrors(['email' => $msg])
                    ->with('error', $msg)
                    ->onlyInput('email');
            }

            // 3. Check active state
            if (!$user->is_active) {
                Auth::logout();
                $msg = 'Akun Anda dinonaktifkan. Silakan hubungi admin.';
                return back()->withErrors(['email' => $msg])
                    ->with('error', $msg)
                    ->onlyInput('email');
            }

            $request->session()->regenerate();

            AuditLog::log('LOGIN', 'User', $user->id, null, ['role' => $user->role]);

            if ($user->isGuide()) {
                return redirect()->intended(route('guide.dashboard'));
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'in:STAFF,GUIDE'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar di sistem.',
            'role.required' => 'Pilih peran (role) pendaftaran Anda.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => false,
            'approval_status' => 'PENDING',
            'email_verified_at' => null,
        ]);

        // Send Email Verification Link
        $user->sendEmailVerificationNotification();

        AuditLog::log('REGISTER', 'User', $user->id, null, [
            'email' => $user->email,
            'role' => $user->role,
            'approval_status' => 'PENDING',
        ]);

        return redirect()->route('verification.notice')
            ->with('registered_email', $user->email)
            ->with('success', "Pendaftaran berhasil! Link verifikasi telah dikirimkan ke {$user->email}. Silakan cek kotak masuk email Anda.");
    }

    public function showVerifyNotice(Request $request)
    {
        $email = session('registered_email') ?? $request->query('email');
        return view('auth.verify-notice', compact('email'));
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Link verifikasi tidak valid atau tidak cocok.');
        }

        if (!$request->hasValidSignature()) {
            return redirect()->route('verification.notice', ['email' => $user->email])
                ->with('error', 'Link verifikasi telah kadaluarsa. Silakan kirim ulang link verifikasi baru.');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
            AuditLog::log('VERIFY_EMAIL', 'User', $user->id);
        }

        return view('auth.verified-success', compact('user'));
    }

    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Alamat email tidak ditemukan di sistem.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')
                ->with('info', 'Alamat email Anda sudah terverifikasi sebelumnya. Silakan tunggu persetujuan (ACC) dari Administrator.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', "Link verifikasi baru telah berhasil dikirimkan ke {$user->email}.");
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::log('LOGOUT', 'User', Auth::id());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }
}
