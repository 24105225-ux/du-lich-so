<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(
    Illuminate\Contracts\Console\Kernel::class
);

$kernel->bootstrap();

$adminId = (int)($argv[1] ?? 0);
$classId = (int)($argv[2] ?? 0);
$scheduleId = (int)($argv[3] ?? 0);
$studentCount = (int)($argv[4] ?? 1);

try {
    $admin = App\Models\User::findOrFail($adminId);

    $registration = app(
        App\Services\ClassRegistrationService::class
    )->register(
        $admin,
        $classId,
        $scheduleId,
        $studentCount
    );

    echo json_encode([
        'ok' => true,
        'registration_id' => $registration->id,
        'status' => $registration->status,
        'hold_expires_at' => optional(
            $registration->hold_expires_at
        )->toDateTimeString(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}