<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
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
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('login');
    }
}
