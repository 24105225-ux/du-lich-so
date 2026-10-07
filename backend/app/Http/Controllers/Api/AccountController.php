<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function csrfToken(Request $request)
    {
        return response()->json([
            'token' => $request->session()->token(),
        ])->header('Cache-Control', 'no-store');
    }
}
