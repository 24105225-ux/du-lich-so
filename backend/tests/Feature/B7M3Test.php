<?php

namespace Tests\Feature;

use App\Models\ClassRegistration;
use App\Models\Order;
use App\Models\ProgramSchedule;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\ClassRegistrationService;
use App\Services\PythonDataService;
use App\Services\TrialPaymentService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class B7M3Test extends TestCase
{
    private function findPair(
        User $admin
    ): array {
        $service = app(
            ClassRegistrationService::class
        );

        $schedules = ProgramSchedule::query()
            ->where('status', 'open')
            ->whereDate(
                'trip_date',
                '>=',
                today()
            )
            ->orderBy('trip_date')
            ->orderBy('id')
            ->get();

        $classes = SchoolClass::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        foreach ($schedules as $schedule) {

            foreach ($classes as $class) {

                if (
                    $class->students()->count() < 1
                ) {
                    continue;
                }

                $active =
                    ClassRegistration::query()
                        ->where(
                            'class_id',
                            $class->id
                        )
                        ->where(
                            'schedule_id',
                            $schedule->id
                        )
                        ->whereIn(
                            'status',
                            [
                                'pending',
                                'approved',
                            ]
                        )
                        ->exists();

                if ($active) {
                    continue;
                }

                $remaining =
                    $service->remainingSeats(
                        $admin,
                        $class->id,
                        $schedule->id
                    );

                if ($remaining >= 1) {

                    return [
                        'class_id' =>
                            $class->id,

                        'schedule_id' =>
                            $schedule->id,
                    ];
                }
            }
        }

        $this->fail(
            'Khong tim duoc class/schedule test B7'
        );
    }

    public function test_trial_payment_success()
    {
        $admin = User::query()
            ->where('role', 'admin')
            ->firstOrFail();

        $pair = $this->findPair($admin);

        $registrations =
            app(ClassRegistrationService::class);

        $registration =
            $registrations->register(
                $admin,
                $pair['class_id'],
                $pair['schedule_id'],
                1
            );

        $payments =
            app(TrialPaymentService::class);

        $order =
            $payments->createOrder(
                $admin,
                $registration
            );

        $paid = $payments->pay($order);

        $this->assertSame(
            'paid',
            $paid->status
        );

        $this->assertNotNull(
            $paid->paid_at
        );

        $this->assertSame(
            'approved',
            $registration->fresh()->status
        );

        $this->assertSame(
            1,
            $paid->payments()
                ->where('status', 'success')
                ->count()
        );

        $paid->payments()->delete();
        $paid->delete();

        $registration->delete();
    }

    public function test_cancelled_payment_keeps_order_pending()
    {
        $admin = User::query()
            ->where('role', 'admin')
            ->firstOrFail();

        $pair = $this->findPair($admin);

        $registration =
            app(ClassRegistrationService::class)
                ->register(
                    $admin,
                    $pair['class_id'],
                    $pair['schedule_id'],
                    1
                );

        $payments =
            app(TrialPaymentService::class);

        $order =
            $payments->createOrder(
                $admin,
                $registration
            );

        $payment =
            $payments->cancelPayment($order);

        $this->assertSame(
            'cancelled',
            $payment->status
        );

        $this->assertSame(
            'pending',
            $order->fresh()->status
        );

        $this->assertSame(
            'pending',
            $registration->fresh()->status
        );

        $order->payments()->delete();
        $order->delete();
        $registration->delete();
    }

    public function test_refund_before_payment_is_rejected()
    {
        $admin = User::query()
            ->where('role', 'admin')
            ->firstOrFail();

        $pair = $this->findPair($admin);

        $registration =
            app(ClassRegistrationService::class)
                ->register(
                    $admin,
                    $pair['class_id'],
                    $pair['schedule_id'],
                    1
                );

        $payments =
            app(TrialPaymentService::class);

        $order =
            $payments->createOrder(
                $admin,
                $registration
            );

        $thrown = false;

        try {
            $payments->refund($order);
        } catch (\RuntimeException $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown);

        $order->payments()->delete();
        $order->delete();
        $registration->delete();
    }

    public function test_admin_dashboard_has_four_indicators_and_two_charts()
    {
        $admin = User::query()
            ->where('role', 'admin')
            ->firstOrFail();

        $response = $this
            ->actingAs($admin)
            ->get(
                route('m3-admin-dashboard.index')
            );

        $response->assertStatus(200);

        $response->assertSee(
            'Tổng đơn hàng'
        );

        $response->assertSee(
            'Đơn đã thanh toán'
        );

        $response->assertSee(
            'Đơn chờ thanh toán'
        );

        $response->assertSee(
            'Doanh thu'
        );

        $response->assertSee(
            'Biểu đồ 1 – Trạng thái đơn'
        );

        $response->assertSee(
            'Biểu đồ 2 – Doanh thu 7 ngày'
        );
    }

    public function test_python_fallback_when_service_is_unavailable()
    {
        $program = \App\Models\Program::query()
            ->published()
            ->orderByDesc('id')
            ->firstOrFail();

        Cache::forget(
            'dt17-recommend:' . $program->id
        );

        Config::set(
            'services.python.url',
            'http://127.0.0.1:8999'
        );

        $result =
            app(PythonDataService::class)
                ->recommend($program->id);

        $this->assertSame(
            'fallback',
            $result['source']
        );

        Config::set(
            'services.python.url',
            env(
                'PY_SERVICE_URL',
                'http://127.0.0.1:8001'
            )
        );
    }
}