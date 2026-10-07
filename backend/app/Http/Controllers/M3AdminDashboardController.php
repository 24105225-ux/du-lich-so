<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\TrialPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class M3AdminDashboardController extends Controller
{
    private function adminOnly(): void
    {
        $user = auth()->user();

        abort_unless($user, 401);

        abort_unless(
            $user->status === 'active',
            403
        );

        abort_unless(
            $user->role === 'admin',
            403
        );
    }

    public function index(
        TrialPaymentService $payments
    ) {
        $this->adminOnly();

        $metrics = $payments->dashboardMetrics();

        Log::debug(
            'M3_ADMIN_DASHBOARD_VIEW',
            [
                'user_id' => auth()->id(),
                'total_orders' => $metrics['total'],
            ]
        );

        return view(
            'admin.m3-dashboard',
            compact('metrics')
        );
    }

    public function export()
    {
        $this->adminOnly();

        $filename =
            'm3-orders-' .
            now()->format('Ymd-His') .
            '.csv';

        $rows = Order::query()
            ->with([
                'registration',
                'user',
            ])
            ->latest('id')
            ->get();

        Log::info(
            'M3_ADMIN_EXPORT',
            [
                'user_id' => auth()->id(),
                'rows' => $rows->count(),
            ]
        );

        return response()->streamDownload(
            function () use ($rows) {

                $out = fopen(
                    'php://output',
                    'w'
                );

                fputcsv(
                    $out,
                    [
                        'ID',
                        'Order No',
                        'User ID',
                        'Registration ID',
                        'Amount',
                        'Status',
                        'Paid At',
                        'Created At',
                    ]
                );

                foreach ($rows as $row) {

                    fputcsv(
                        $out,
                        [
                            $row->id,
                            $row->order_no,
                            $row->user_id,
                            $row->class_registration_id,
                            $row->amount,
                            $row->status,
                            optional(
                                $row->paid_at
                            )->toDateTimeString(),
                            optional(
                                $row->created_at
                            )->toDateTimeString(),
                        ]
                    );
                }

                fclose($out);
            },
            $filename,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }
}