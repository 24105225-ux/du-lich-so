<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class RoleDashboardController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        $title = match ($user->role) {
            'admin' =>
                'Bảng điều khiển quản trị',

            'school' =>
                'Bảng điều khiển nhà trường',

            'parent' =>
                'Bảng điều khiển phụ huynh',

            'organizer' =>
                'Bảng điều khiển đơn vị tổ chức',

            default =>
                'Bảng điều khiển',
        };

        return view(
            'dashboard.role',
            [
                'title' => $title,
                'role' => $user->role,
            ]
        );
    }
}
