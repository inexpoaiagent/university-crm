<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['nullable', 'string', 'max:190'],
            'email' => ['nullable', 'string', 'max:190'],
            'password' => ['required', 'string'],
        ]);
        $login = trim((string) ($data['login'] ?? $data['email'] ?? ''));
        if ($login === '') {
            return back()->withErrors(['login' => 'Login is required'])->withInput();
        }

        try {
            $user = User::query()
                ->where(function ($query) use ($login) {
                    $query->whereRaw('LOWER(email) = ?', [mb_strtolower($login)])
                        ->orWhere('name', $login);
                })
                ->where('role_slug', '!=', 'student')
                ->whereNull('deleted_at')
                ->first();
        } catch (QueryException $e) {
            report($e);
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['login' => 'Database connection failed. Please start MySQL and try again.']);
        }
        if (!$user || !$user->is_active || !Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['login' => 'Invalid credentials'])->withInput();
        }
        Auth::guard('student')->logout();
        Auth::guard('crm')->login($user, false);
        $request->session()->regenerate();

        return redirect('/dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('crm')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
