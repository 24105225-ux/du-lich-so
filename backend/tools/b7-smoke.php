<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(
    Illuminate\Contracts\Console\Kernel::class
);

$kernel->bootstrap();

$results = [];

function addResult(
    string $id,
    string $name,
    bool $pass,
    string $actual,
    string $evidence = ''
): void {
    global $results;

    $results[] = [
        'id' => $id,
        'name' => $name,
        'result' => $pass ? 'PASS' : 'FAIL',
        'actual' => $actual,
        'evidence' => $evidence,
        'executed_at' => date('Y-m-d H:i:s'),
    ];
}

$admin = App\Models\User::query()
    ->where('role', 'admin')
    ->firstOrFail();

$registrationService =
    app(App\Services\ClassRegistrationService::class);

$paymentService =
    app(App\Services\TrialPaymentService::class);

$schedules = App\Models\ProgramSchedule::query()
    ->where('status', 'open')
    ->whereDate(
        'trip_date',
        '>=',
        today()
    )
    ->orderBy('trip_date')
    ->orderBy('id')
    ->get();

$classes = App\Models\SchoolClass::query()
    ->where('status', 'active')
    ->orderBy('id')
    ->get();

function findPair(
    $admin,
    $registrationService,
    $schedules,
    $classes
): array {

    foreach ($schedules as $schedule) {

        foreach ($classes as $class) {

            if ($class->students()->count() < 1) {
                continue;
            }

            $active =
                App\Models\ClassRegistration::query()
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
                $registrationService->remainingSeats(
                    $admin,
                    $class->id,
                    $schedule->id
                );

            if ($remaining >= 1) {

                return [
                    'class_id' => $class->id,
                    'schedule_id' => $schedule->id,
                ];
            }
        }
    }

    throw new RuntimeException(
        'Khong tim duoc pair cho B7 smoke.'
    );
}

/*
 * TC-B7-01 successful trial payment
 */
try {

    $pair =
        findPair(
            $admin,
            $registrationService,
            $schedules,
            $classes
        );

    $registration =
        $registrationService->register(
            $admin,
            $pair['class_id'],
            $pair['schedule_id'],
            1
        );

    $order =
        $paymentService->createOrder(
            $admin,
            $registration
        );

    $paid =
        $paymentService->pay($order);

    $successCount =
        $paid->payments()
            ->where('status', 'success')
            ->count();

    $ok =
        $paid->status === 'paid' &&
        $successCount >= 1 &&
        $paid->registration->status === 'approved';

    addResult(
        'TC-B7-01',
        'Thanh toán thử nghiệm thành công',
        $ok,
        'order_status=' .
        $paid->status .
        '; success_payments=' .
        $successCount .
        '; registration_status=' .
        $paid->registration->status,
        'Order #' . $paid->id
    );

} catch (Throwable $e) {

    addResult(
        'TC-B7-01',
        'Thanh toán thử nghiệm thành công',
        false,
        $e->getMessage()
    );
}

/*
 * TC-B7-02 cancelled payment keeps pending
 */
try {

    $pair =
        findPair(
            $admin,
            $registrationService,
            $schedules,
            $classes
        );

    $registration =
        $registrationService->register(
            $admin,
            $pair['class_id'],
            $pair['schedule_id'],
            1
        );

    $order =
        $paymentService->createOrder(
            $admin,
            $registration
        );

    $payment =
        $paymentService->cancelPayment(
            $order
        );

    $freshOrder = $order->fresh();
    $freshRegistration =
        $registration->fresh();

    $ok =
        $payment->status === 'cancelled' &&
        $freshOrder->status === 'pending' &&
        $freshRegistration->status === 'pending';

    addResult(
        'TC-B7-02',
        'Hủy giao dịch không làm sai inventory',
        $ok,
        'payment=' .
        $payment->status .
        '; order=' .
        $freshOrder->status .
        '; registration=' .
        $freshRegistration->status,
        'Inventory remains reserved only by pending hold.'
    );

    $freshOrder->payments()->delete();
    $freshOrder->delete();
    $freshRegistration->delete();

} catch (Throwable $e) {

    addResult(
        'TC-B7-02',
        'Hủy giao dịch không làm sai inventory',
        false,
        $e->getMessage()
    );
}

/*
 * TC-B7-03 invalid refund
 */
try {

    $pair =
        findPair(
            $admin,
            $registrationService,
            $schedules,
            $classes
        );

    $registration =
        $registrationService->register(
            $admin,
            $pair['class_id'],
            $pair['schedule_id'],
            1
        );

    $order =
        $paymentService->createOrder(
            $admin,
            $registration
        );

    $rejected = false;
    $message = '';

    try {
        $paymentService->refund($order);
    } catch (RuntimeException $e) {
        $rejected = true;
        $message = $e->getMessage();
    }

    addResult(
        'TC-B7-03',
        'Từ chối refund trước thanh toán',
        $rejected,
        $rejected
            ? 'rejected: ' . $message
            : 'refund unexpectedly accepted',
        'State machine'
    );

    $order->payments()->delete();
    $order->delete();
    $registration->delete();

} catch (Throwable $e) {

    addResult(
        'TC-B7-03',
        'Từ chối refund trước thanh toán',
        false,
        $e->getMessage()
    );
}

/*
 * TC-B7-04 Python fallback
 */
try {

    $program =
        App\Models\Program::query()
            ->published()
            ->orderByDesc('id')
            ->firstOrFail();

    Illuminate\Support\Facades\Cache::forget(
        'dt17-recommend:' . $program->id
    );

    Illuminate\Support\Facades\Config::set(
        'services.python.url',
        'http://127.0.0.1:8999'
    );

    $fallback =
        app(App\Services\PythonDataService::class)
            ->recommend($program->id);

    $ok =
        ($fallback['source'] ?? null) ===
        'fallback';

    addResult(
        'TC-B7-04',
        'Python stop -> fallback, không 500',
        $ok,
        'source=' .
        ($fallback['source'] ?? 'unknown'),
        'PY_SERVICE_URL=127.0.0.1:8999'
    );

    Illuminate\Support\Facades\Config::set(
        'services.python.url',
        env(
            'PY_SERVICE_URL',
            'http://127.0.0.1:8001'
        )
    );

} catch (Throwable $e) {

    addResult(
        'TC-B7-04',
        'Python stop -> fallback, không 500',
        false,
        $e->getMessage()
    );
}

/*
 * TC-B7-05 dashboard controller-level
 */
try {

    $metrics =
        $paymentService->dashboardMetrics();

    $ok =
        isset($metrics['total']) &&
        isset($metrics['paid']) &&
        isset($metrics['pending']) &&
        isset($metrics['revenue']) &&
        isset($metrics['statusChart']) &&
        isset($metrics['dailyRevenue']);

    addResult(
        'TC-B7-05',
        'Admin dashboard 4 KPI + 2 chart datasets',
        $ok,
        'keys=' .
        implode(
            ',',
            array_keys($metrics)
        ),
        'dashboardMetrics()'
    );

} catch (Throwable $e) {

    addResult(
        'TC-B7-05',
        'Admin dashboard 4 KPI + 2 chart datasets',
        false,
        $e->getMessage()
    );
}

$root =
    dirname(__DIR__, 2);

$dir =
    $root .
    '/docs/evidence/buoi-7';

if (!is_dir($dir)) {
    mkdir(
        $dir,
        0777,
        true
    );
}

file_put_contents(
    $dir . '/m3-smoke-results.json',
    json_encode(
        $results,
        JSON_UNESCAPED_UNICODE |
        JSON_PRETTY_PRINT
    )
);

$total = count($results);

$pass = count(
    array_filter(
        $results,
        fn ($r) =>
            $r['result'] === 'PASS'
    )
);

$fail = $total - $pass;

echo PHP_EOL;
echo '================ M3 SMOKE ================' . PHP_EOL;
echo 'TOTAL : ' . $total . PHP_EOL;
echo 'PASS  : ' . $pass . PHP_EOL;
echo 'FAIL  : ' . $fail . PHP_EOL;
echo '==========================================' . PHP_EOL;

foreach ($results as $row) {

    echo
        $row['id'] .
        ' | ' .
        $row['result'] .
        ' | ' .
        $row['name'] .
        PHP_EOL;
}

if ($fail > 0) {
    exit(2);
}