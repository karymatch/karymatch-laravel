<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()    { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($cred, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))
                ->with('error', 'Email atau sandi salah.');
        }

        $request->session()->regenerate();

        return redirect($this->homeFor(Auth::user()));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'role'         => ['required', 'in:pekerja,perusahaan'],   // admin tidak bisa mendaftar sendiri
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
            'company_name' => ['required_if:role,perusahaan', 'nullable', 'string', 'max:150'],
            'city'         => ['nullable', 'string', 'max:100'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'],   // di-hash otomatis oleh model
            ]);
            $user->forceFill(['role' => $data['role']])->save();

            if ($data['role'] === 'perusahaan') {
                Company::create([
                    'user_id' => $user->id,
                    'name'    => $data['company_name'],
                    'city'    => $data['city'] ?? null,
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($this->homeFor($user))
            ->with('ok', $user->role === 'perusahaan'
                ? 'Akun dibuat. Perusahaan Anda menunggu verifikasi admin sebelum bisa memasang iklan.'
                : 'Akun dibuat. Selamat datang!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function homeFor(User $user): string
    {
        return match ($user->role) {
            'admin'      => route('admin.dashboard'),
            'perusahaan' => route('company.dashboard'),
            default      => route('home'),
        };
    }
}
