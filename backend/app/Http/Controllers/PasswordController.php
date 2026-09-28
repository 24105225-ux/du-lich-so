<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('auth.password');
    }

    public function update(
        ChangePasswordRequest $request
    ): RedirectResponse {
        $user = $request->user();

        if (
            !Hash::check(
                $request->current_password,
                $user->password_hash
            )
        ) {
            return back()
                ->withErrors([
                    'current_password' =>
                        'Mật khẩu hiện tại không đúng.',
                ]);
        }

        $newPassword =
            Hash::make(
                $request->password
            );

        $user->update([
            'password_hash' =>
                $newPassword,
        ]);

        Auth::logoutOtherDevices(
            $request->password
        );

        $request->session()->regenerate();

        return back()->with(
            'status',
            'Đổi mật khẩu thành công.'
        );
    }
}
