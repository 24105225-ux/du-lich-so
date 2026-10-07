<?php

namespace App\Services;

use App\Models\ClassRegistration;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class TrialPaymentService
{
    public function createOrder(
        $user,
        ClassRegistration $registration
    ): Order {
        return DB::transaction(function () use (
            $user,
            $registration
        ) {

            $registration = ClassRegistration::query()
                ->lockForUpdate()
                ->findOrFail($registration->id);

            $existing = Order::query()
                ->where('user_id', $user->id)
                ->where(
                    'class_registration_id',
                    $registration->id
                )
                ->whereIn('status', ['pending', 'paid'])
                ->latest('id')
                ->first();

            if ($existing) {
                return $existing;
            }

            $cost = (float) DB::table('program_schedules')
                ->join(
                    'programs',
                    'programs.id',
                    '=',
                    'program_schedules.program_id'
                )
                ->where(
                    'program_schedules.id',
                    $registration->schedule_id
                )
                ->value('programs.base_cost_per_student');

            $amount =
                round(
                    $cost * max(1, (int)$registration->student_count),
                    2
                );

            $order = Order::query()->create([
                'user_id' => $user->id,
                'class_registration_id' => $registration->id,
                'order_no' =>
                    'M3-' .
                    now()->format('YmdHis') .
                    '-' .
                    strtoupper(
                        substr(
                            bin2hex(random_bytes(4)),
                            0,
                            8
                        )
                    ),
                'amount' => $amount,
                'status' => 'pending',
            ]);

            Log::info(
                'M3_ORDER_CREATED',
                [
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'registration_id' => $registration->id,
                    'amount' => $amount,
                ]
            );

            return $order;
        });
    }

    public function pay(
        Order $order
    ): Order {
        return DB::transaction(function () use ($order) {

            $order = Order::query()
                ->lockForUpdate()
                ->with('registration')
                ->findOrFail($order->id);

            if ($order->status !== 'pending') {
                throw new RuntimeException(
                    'Đơn không ở trạng thái pending.'
                );
            }

            $registration = ClassRegistration::query()
                ->lockForUpdate()
                ->findOrFail(
                    $order->class_registration_id
                );

            if (
                $registration->status === 'pending' &&
                $registration->hold_expires_at &&
                $registration->hold_expires_at->lte(now())
            ) {
                $registration->update([
                    'status' => 'cancelled',
                ]);

                Log::warning(
                    'M3_PAYMENT_EXPIRED_HOLD',
                    [
                        'order_id' => $order->id,
                        'registration_id' =>
                            $registration->id,
                    ]
                );

                throw new RuntimeException(
                    'Thời gian giữ chỗ đã hết.'
                );
            }

            $transactionCode =
                'TRIAL-' .
                now()->format('YmdHis') .
                '-' .
                strtoupper(
                    substr(
                        bin2hex(random_bytes(4)),
                        0,
                        8
                    )
                );

            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'transaction_code' =>
                    $transactionCode,
                'amount' => $order->amount,
                'status' => 'success',
                'method' => 'trial',
                'paid_at' => now(),
                'payload' => [
                    'sandbox' => true,
                    'result' => 'success',
                ],
            ]);

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $registration->update([
                'status' => 'approved',
                'hold_expires_at' => null,
            ]);

            Log::info(
                'M3_PAYMENT_SUCCESS',
                [
                    'order_id' => $order->id,
                    'transaction_code' =>
                        $payment->transaction_code,
                    'amount' => $order->amount,
                ]
            );

            Log::info(
                'M3_NOTIFICATION_SIMULATED',
                [
                    'order_id' => $order->id,
                    'message' =>
                        'Thông báo thanh toán thành công đã được ghi nhận.',
                ]
            );

            return $order->fresh([
                'registration',
                'payments',
            ]);
        });
    }

    public function cancelPayment(
        Order $order
    ): Payment {
        return DB::transaction(function () use ($order) {

            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->status !== 'pending') {
                throw new RuntimeException(
                    'Chỉ được hủy giao dịch khi đơn đang pending.'
                );
            }

            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'transaction_code' =>
                    'TRIAL-CANCEL-' .
                    now()->format('YmdHis') .
                    '-' .
                    strtoupper(
                        substr(
                            bin2hex(random_bytes(4)),
                            0,
                            8
                        )
                    ),
                'amount' => $order->amount,
                'status' => 'cancelled',
                'method' => 'trial',
                'payload' => [
                    'sandbox' => true,
                    'result' => 'cancelled',
                ],
            ]);

            Log::warning(
                'M3_PAYMENT_CANCELLED',
                [
                    'order_id' => $order->id,
                    'transaction_code' =>
                        $payment->transaction_code,
                ]
            );

            Log::info(
                'M3_PAYMENT_CANCELLED_KEEP_PENDING',
                [
                    'order_id' => $order->id,
                ]
            );

            return $payment;
        });
    }

    public function failPayment(
        Order $order
    ): Payment {
        return DB::transaction(function () use ($order) {

            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->status !== 'pending') {
                throw new RuntimeException(
                    'Không thể tạo failed payment cho đơn không pending.'
                );
            }

            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'transaction_code' =>
                    'TRIAL-FAIL-' .
                    now()->format('YmdHis') .
                    '-' .
                    strtoupper(
                        substr(
                            bin2hex(random_bytes(4)),
                            0,
                            8
                        )
                    ),
                'amount' => $order->amount,
                'status' => 'failed',
                'method' => 'trial',
                'payload' => [
                    'sandbox' => true,
                    'result' => 'failed',
                ],
            ]);

            Log::warning(
                'M3_PAYMENT_FAILED',
                [
                    'order_id' => $order->id,
                    'transaction_code' =>
                        $payment->transaction_code,
                ]
            );

            return $payment;
        });
    }

    public function refund(
        Order $order
    ): Payment {
        return DB::transaction(function () use ($order) {

            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($order->status !== 'paid') {

                Log::error(
                    'M3_INVALID_REFUND',
                    [
                        'order_id' => $order->id,
                        'status' => $order->status,
                    ]
                );

                throw new RuntimeException(
                    'Chỉ được hoàn tiền sau khi thanh toán thành công.'
                );
            }

            if (!$order->canTransitionTo('refunded')) {

                Log::error(
                    'M3_INVALID_STATE_TRANSITION',
                    [
                        'order_id' => $order->id,
                        'from' => $order->status,
                        'to' => 'refunded',
                    ]
                );

                throw new RuntimeException(
                    'Chuyển trạng thái đơn không hợp lệ.'
                );
            }

            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'transaction_code' =>
                    'TRIAL-REFUND-' .
                    now()->format('YmdHis') .
                    '-' .
                    strtoupper(
                        substr(
                            bin2hex(random_bytes(4)),
                            0,
                            8
                        )
                    ),
                'amount' => $order->amount,
                'status' => 'refunded',
                'method' => 'trial',
                'payload' => [
                    'sandbox' => true,
                    'result' => 'refunded',
                ],
            ]);

            $order->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);

            Log::info(
                'M3_REFUND_SUCCESS',
                [
                    'order_id' => $order->id,
                    'transaction_code' =>
                        $payment->transaction_code,
                ]
            );

            return $payment;
        });
    }

    public function dashboardMetrics(): array
    {
        $total = Order::query()->count();

        $paid = Order::query()
            ->where('status', 'paid')
            ->count();

        $pending = Order::query()
            ->where('status', 'pending')
            ->count();

        $cancelled = Order::query()
            ->where('status', 'cancelled')
            ->count();

        $refunded = Order::query()
            ->where('status', 'refunded')
            ->count();

        $revenue = (float) Order::query()
            ->where('status', 'paid')
            ->sum('amount');

        $statusChart = [
            'pending' => $pending,
            'paid' => $paid,
            'cancelled' => $cancelled,
            'refunded' => $refunded,
        ];

        $dailyRevenue = [];

        for ($i = 6; $i >= 0; $i--) {

            $date = now()
                ->subDays($i)
                ->toDateString();

            $dailyRevenue[$date] =
                (float) Order::query()
                    ->where('status', 'paid')
                    ->whereDate(
                        'paid_at',
                        $date
                    )
                    ->sum('amount');
        }

        return [
            'total' => $total,
            'paid' => $paid,
            'pending' => $pending,
            'revenue' => $revenue,
            'statusChart' => $statusChart,
            'dailyRevenue' => $dailyRevenue,
        ];
    }
}