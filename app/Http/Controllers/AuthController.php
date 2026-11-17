<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectForUser(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            return $this->redirectForUser($user)
                ->with('success', "Selamat datang kembali, {$user->name}!");
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'Email atau password yang Anda masukkan tidak sesuai.']);
    }

    /**
     * One-Click Quick Demo Login for convenient presentation/testing.
     */
    public function quickLogin(Request $request, ?string $role = null): RedirectResponse
    {
        $role = strtolower($role ?: $request->input('role', 'customer'));

        $targetEmail = match ($role) {
            'admin' => 'admin@sembako.test',
            'driver', 'courier' => 'driver@sembako.test',
            default => 'pelanggan@sembako.test',
        };

        $user = User::where('email', $targetEmail)->first()
            ?? User::where('role', $role)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', "Akun demo untuk role {$role} tidak ditemukan. Silakan jalankan seeder terlebih dahulu.");
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return $this->redirectForUser($user, false)
            ->with('success', "⚡ 1-Klik Login berhasil! Anda masuk sebagai {$user->name} ({$user->role_label}).");
    }

    /**
     * Show the customer registration form.
     */
    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectForUser(Auth::user());
        }

        return view('auth.register');
    }

    /**
     * Handle customer registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk menggunakan akun Anda.',
            'phone.required' => 'Nomor WhatsApp / telepon wajib diisi untuk kontak pengantaran.',
            'phone.unique' => 'Nomor telepon ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'address' => $validated['address'] ?? null,
            'latitude' => -7.0125000,
            'longitude' => 110.4320000,
            'is_online' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', "Selamat bergabung, {$user->name}! Akun pelanggan Anda berhasil dibuat.");
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah berhasil keluar dari akun.');
    }

    /**
     * Redirect user based on their specific role.
     */
    public function redirectForUser(User $user, bool $useIntended = true): RedirectResponse
    {
        $targetRoute = match (true) {
            $user->isAdmin() => route('admin.dashboard'),
            $user->isDriver() => route('driver.dashboard'),
            default => route('home'),
        };

        if ($useIntended && session()->has('url.intended')) {
            return redirect()->intended($targetRoute);
        }

        return redirect()->to($targetRoute);
    }
}
