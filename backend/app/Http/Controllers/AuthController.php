<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private AuditLogService $auditLogs)
    {
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function registerStore(
        RegisterRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $user = User::create([
            'email' => $validated['email'],
            'password_hash' => Hash::make(
                $validated['password']
            ),
            'role' => 'parent',
            'status' => 'active',
        ]);

        $this->auditLogs->record(
            $request,
            'REGISTER',
            'user',
            $user->id,
            null,
            [
                'role' => $user->role,
                'status' => $user->status,
            ],
        );

        return redirect()
            ->route('login')
            ->with(
                'status',
                'Đăng ký tài khoản thành công. Vui lòng đăng nhập.'
            );
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(
        LoginRequest $request
    ): RedirectResponse {
        $credentials = $request->validated();

        if (
            !Auth::attempt([
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ])
        ) {
            RateLimiter::hit(
                'login:' .
                $request->ip() .
                '|' .
                $credentials['email'],
                60
            );

            return back()
                ->withInput(
                    $request->only('email')
                )
                ->withErrors([
                    'email' =>
                        'Thông tin đăng nhập không đúng.',
                ]);
        }

        RateLimiter::clear(
            'login:' .
            $request->ip() .
            '|' .
            $credentials['email']
        );

        $request->session()->regenerate();

        $user = $request->user();

        if ($user) {
            $user->forceFill(['last_login_at' => now()])->save();
            $this->auditLogs->record(
                $request,
                'LOGIN',
                'user',
                $user->id,
                null,
                ['role' => $user->role]
            );
        }

        if (
            $user &&
            Hash::needsRehash(
                $user->password_hash
            )
        ) {
            $user->update([
                'password_hash' => Hash::make(
                    $credentials['password']
                ),
            ]);
        }

        return redirect()
            ->intended(
                route('dashboard')
            );
    }

    public function destroy(
        Request $request
    ): RedirectResponse {
        $user = $request->user();

        if ($user) {
            $this->auditLogs->record(
                $request,
                'LOGOUT',
                'user',
                $user->id
            );
        }

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('login');
    }
}