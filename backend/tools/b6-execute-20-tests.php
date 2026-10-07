<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(
    Illuminate\Contracts\Console\Kernel::class
);

$kernel->bootstrap();

$results = [];
$baseUrl = 'http://127.0.0.1:8000';

function addResult(
    string $id,
    string $name,
    string $expected,
    string $actual,
    bool $pass,
    string $evidence = ''
): void {
    global $results;

    $results[] = [
        'id' => $id,
        'name' => $name,
        'expected' => $expected,
        'actual' => $actual,
        'result' => $pass ? 'PASS' : 'FAIL',
        'evidence' => $evidence,
        'executed_at' => date('Y-m-d H:i:s'),
    ];
}

function jsonResponseData($response): array
{
    $json = $response->json();

    if (!is_array($json)) {
        return [];
    }

    return $json;
}

$program = App\Models\Program::query()
    ->published()
    ->orderBy('id')
    ->first();

if (!$program) {
    throw new RuntimeException('Khong tim thay published program');
}

$admin = App\Models\User::query()
    ->where('role', 'admin')
    ->firstOrFail();

$service = app(
    App\Services\ClassRegistrationService::class
);

/*
 * Chon 1 cap class/schedule an toan cho test.
 */
$pairClass = null;
$pairSchedule = null;

$schedules = App\Models\ProgramSchedule::query()
    ->where('status', 'open')
    ->whereDate('trip_date', '>=', today())
    ->orderBy('trip_date')
    ->orderBy('id')
    ->get();

$classes = App\Models\SchoolClass::query()
    ->where('status', 'active')
    ->orderBy('id')
    ->get();

foreach ($schedules as $schedule) {
    foreach ($classes as $class) {

        $studentTotal = $class->students()->count();

        if ($studentTotal < 2) {
            continue;
        }

        $activeRegistrationExists =
            App\Models\ClassRegistration::query()
                ->where('class_id', $class->id)
                ->where('schedule_id', $schedule->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

        if ($activeRegistrationExists) {
            continue;
        }

        $remaining = $service->remainingSeats(
            $admin,
            $class->id,
            $schedule->id
        );

        if ($remaining >= 2) {
            $pairClass = $class;
            $pairSchedule = $schedule;
            break 2;
        }
    }
}

if (!$pairClass || !$pairSchedule) {
    throw new RuntimeException(
        'Khong tim duoc class/schedule phu hop cho test B6'
    );
}

/*
 * TC-01 API catalog
 */
try {
    $response = Illuminate\Support\Facades\Http::timeout(10)
        ->get($baseUrl . '/api/v1/programs', [
            'per_page' => 5,
        ]);

    addResult(
        'TC-B6-01',
        'Mở API danh mục chương trình',
        'HTTP 200 và có data',
        'HTTP ' . $response->status(),
        $response->successful() &&
        is_array($response->json('data')),
        '/api/v1/programs?per_page=5'
    );
} catch (Throwable $e) {
    addResult(
        'TC-B6-01',
        'Mở API danh mục chương trình',
        'HTTP 200',
        $e->getMessage(),
        false
    );
}

/*
 * TC-02 Keyword
 */
try {
    $response = Illuminate\Support\Facades\Http::timeout(10)
        ->get($baseUrl . '/api/v1/programs', [
            'keyword' => $program->name,
        ]);

    $data = $response->json('data') ?: [];
    $found = false;

    foreach ($data as $item) {
        if (
            isset($item['title']) &&
            stripos($item['title'], $program->name) !== false
        ) {
            $found = true;
            break;
        }
    }

    addResult(
        'TC-B6-02',
        'Tìm kiếm theo từ khóa',
        'Có chương trình phù hợp từ khóa',
        'HTTP ' . $response->status() . '; found=' . ($found ? 'true' : 'false'),
        $response->successful() && $found,
        'keyword=' . $program->name
    );
} catch (Throwable $e) {
    addResult(
        'TC-B6-02',
        'Tìm kiếm theo từ khóa',
        'HTTP 200',
        $e->getMessage(),
        false
    );
}

/*
 * TC-03 education level
 */
try {
    $response = Illuminate\Support\Facades\Http::timeout(10)
        ->get($baseUrl . '/api/v1/programs', [
            'education_level' => $program->education_level,
            'per_page' => 10,
        ]);

    $data = $response->json('data') ?: [];
    $ok = $response->successful();

    foreach ($data as $item) {
        if (
            ($item['education_level'] ?? null)
            !== $program->education_level
        ) {
            $ok = false;
            break;
        }
    }

    addResult(
        'TC-B6-03',
        'Lọc theo cấp học',
        'Các kết quả đúng cấp học',
        'HTTP ' . $response->status(),
        $ok,
        'education_level=' . $program->education_level
    );
} catch (Throwable $e) {
    addResult(
        'TC-B6-03',
        'Lọc theo cấp học',
        'HTTP 200',
        $e->getMessage(),
        false
    );
}

/*
 * TC-04 min price
 */
try {
    $minPrice = (float)$program->base_cost_per_student;

    $response = Illuminate\Support\Facades\Http::timeout(10)
        ->get($baseUrl . '/api/v1/programs', [
            'min_price' => $minPrice,
            'per_page' => 20,
        ]);

    $data = $response->json('data') ?: [];
    $ok = $response->successful();

    foreach ($data as $item) {
        if (
            isset($item['price_per_student']) &&
            (float)$item['price_per_student'] < $minPrice
        ) {
            $ok = false;
            break;
        }
    }

    addResult(
        'TC-B6-04',
        'Lọc giá tối thiểu',
        'Không có kết quả thấp hơn min_price',
        'HTTP ' . $response->status(),
        $ok,
        'min_price=' . $minPrice
    );
} catch (Throwable $e) {
    addResult(
        'TC-B6-04',
        'Lọc giá tối thiểu',
        'HTTP 200',
        $e->getMessage(),
        false
    );
}

/*
 * TC-05 max price
 */
try {
    $maxPrice = (float)$program->base_cost_per_student;

    $response = Illuminate\Support\Facades\Http::timeout(10)
        ->get($baseUrl . '/api/v1/programs', [
            'max_price' => $maxPrice,
            'per_page' => 20,
        ]);

    $data = $response->json('data') ?: [];
    $ok = $response->successful();

    foreach ($data as $item) {
        if (
            isset($item['price_per_student']) &&
            (float)$item['price_per_student'] > $maxPrice
        ) {
            $ok = false;
            break;
        }
    }

    addResult(
        'TC-B6-05',
        'Lọc giá tối đa',
        'Không có kết quả cao hơn max_price',
        'HTTP ' . $response->status(),
        $ok,
        'max_price=' . $maxPrice
    );
} catch (Throwable $e) {
    addResult(
        'TC-B6-05',
        'Lọc giá tối đa',
        'HTTP 200',
        $e->getMessage(),
        false
    );
}

/*
 * TC-06 sort asc
 */
try {
    $response = Illuminate\Support\Facades\Http::timeout(10)
        ->get($baseUrl . '/api/v1/programs', [
            'sort' => 'price_asc',
            'per_page' => 20,
        ]);

    $data = $response->json('data') ?: [];
    $ok = $response->successful();
    $previous = null;

    foreach ($data as $item) {
        $price = (float)($item['price_per_student'] ?? 0);

        if ($previous !== null && $price < $previous) {
            $ok = false;
            break;
        }

        $previous = $price;
    }

    addResult(
        'TC-B6-06',
        'Sắp xếp giá tăng dần',
        'Dữ liệu tăng dần theo giá',
        'HTTP ' . $response->status(),
        $ok,
        'sort=price_asc'
    );
} catch (Throwable $e) {
    addResult(
        'TC-B6-06',
        'Sắp xếp giá tăng dần',
        'HTTP 200',
        $e->getMessage(),
        false
    );
}

/*
 * TC-07 pagination
 */
try {
    $response = Illuminate\Support\Facades\Http::timeout(10)
        ->get($baseUrl . '/api/v1/programs', [
            'per_page' => 5,
            'page' => 1,
        ]);

    $data = $response->json('data') ?: [];
    $meta = $response->json('meta') ?: [];

    $ok =
        $response->successful() &&
        count($data) <= 5 &&
        ((int)($meta['per_page'] ?? 0) === 5);

    addResult(
        'TC-B6-07',
        'Phân trang server-side',
        'per_page=5 và dữ liệu không vượt quá 5',
        'HTTP ' . $response->status() . '; count=' . count($data),
        $ok,
        'page=1&per_page=5'
    );
} catch (Throwable $e) {
    addResult(
        'TC-B6-07',
        'Phân trang server-side',
        'HTTP 200',
        $e->getMessage(),
        false
    );
}

/*
 * TC-08 detail
 */
try {
    $response = Illuminate\Support\Facades\Http::timeout(10)
        ->get($baseUrl . '/api/v1/programs/' . $program->id);

    $ok =
        $response->successful() &&
        (int)$response->json('data.id') === (int)$program->id;

    addResult(
        'TC-B6-08',
        'Xem chi tiết chương trình',
        'Trả đúng chương trình theo ID',
        'HTTP ' . $response->status(),
        $ok,
        '/api/v1/programs/' . $program->id
    );
} catch (Throwable $e) {
    addResult(
        'TC-B6-08',
        'Xem chi tiết chương trình',
        'HTTP 200',
        $e->getMessage(),
        false
    );
}

/*
 * TC-09 routes
 */
try {

    $routes = app('router')->getRoutes();

    $hasCreate = false;
    $hasRemaining = false;
    $hasStore = false;

    foreach ($routes as $route) {

        $uri = '/' . ltrim($route->uri(), '/');
        $methods = $route->methods();

        if (
            ($uri === '/dang-ky' || stripos($uri, 'registr') !== false) &&
            in_array('GET', $methods, true)
        ) {
            $hasCreate = true;
        }

        if (
            stripos($uri, 'remaining') !== false ||
            stripos($uri, 'con-lai') !== false
        ) {
            if (in_array('GET', $methods, true)) {
                $hasRemaining = true;
            }
        }

        if (
            ($uri === '/dang-ky' || stripos($uri, 'registr') !== false) &&
            in_array('POST', $methods, true)
        ) {
            $hasStore = true;
        }
    }

    $ok =
        $hasCreate &&
        $hasRemaining &&
        $hasStore;

    addResult(
        'TC-B6-09',
        'Route đăng ký lớp',
        'Có route GET form + GET số chỗ + POST đăng ký',
        'create=' . ($hasCreate ? 'true' : 'false') .
        '; remaining=' . ($hasRemaining ? 'true' : 'false') .
        '; store=' . ($hasStore ? 'true' : 'false'),
        $ok,
        'router inspection'
    );

} catch (Throwable $e) {

    addResult(
        'TC-B6-09',
        'Route đăng ký lớp',
        '3 route đăng ký tồn tại',
        $e->getMessage(),
        false
    );
}

/*
 * TC-10 remaining seats
 */
$remainingBefore = $service->remainingSeats(
    $admin,
    $pairClass->id,
    $pairSchedule->id
);

addResult(
    'TC-B6-10',
    'Kiểm tra số chỗ còn lại',
    'remainingSeats >= 2',
    'remainingSeats=' . $remainingBefore,
    $remainingBefore >= 2,
    'class=' . $pairClass->id . '; schedule=' . $pairSchedule->id
);

/*
 * TC-11 capacity
 */
$pairSchedule->refresh();

addResult(
    'TC-B6-11',
    'Kiểm tra sức chứa schedule',
    'capacity > 0',
    'capacity=' . $pairSchedule->capacity,
    (int)$pairSchedule->capacity > 0,
    'schedule=' . $pairSchedule->id
);

/*
 * TC-12 student count
 */
$studentTotal = $pairClass->students()->count();

addResult(
    'TC-B6-12',
    'Kiểm tra sĩ số lớp',
    'Lớp có ít nhất 2 học sinh cho bài test',
    'student_count=' . $studentTotal,
    $studentTotal >= 2,
    'class=' . $pairClass->id
);

/*
 * TC-13 create registration
 */
$testRegistrationId = null;
$testRegistrationId2 = null;

try {
    $registration = $service->register(
        $admin,
        $pairClass->id,
        $pairSchedule->id,
        1
    );

    $testRegistrationId = $registration->id;

    $ok =
        $registration->status === 'pending';

    addResult(
        'TC-B6-13',
        'Tạo đăng ký đặt chỗ',
        'Tạo record pending',
        'id=' . $registration->id . '; status=' . $registration->status,
        $ok,
        'class=' . $pairClass->id . '; schedule=' . $pairSchedule->id
    );
} catch (Throwable $e) {
    addResult(
        'TC-B6-13',
        'Tạo đăng ký đặt chỗ',
        'Tạo pending thành công',
        $e->getMessage(),
        false
    );
}

/*
 * TC-14 hold 15 minutes
 */
if ($testRegistrationId) {

    $reg = App\Models\ClassRegistration::findOrFail(
        $testRegistrationId
    );

    $expectedTs = now()->addMinutes(15)->timestamp;
    $actualTs = $reg->hold_expires_at
        ? $reg->hold_expires_at->timestamp
        : 0;

    $diff = abs($actualTs - $expectedTs);

    addResult(
        'TC-B6-14',
        'Giữ chỗ có thời hạn 15 phút',
        'hold_expires_at xấp xỉ hiện tại + 15 phút',
        'difference=' . $diff . 's',
        $reg->hold_expires_at !== null && $diff <= 10,
        'registration=' . $reg->id
    );
}

/*
 * TC-15 held seats
 */
if ($testRegistrationId) {

    $remainingAfterHold = $service->remainingSeats(
        $admin,
        $pairClass->id,
        $pairSchedule->id
    );

    $ok = $remainingAfterHold === ($remainingBefore - 1);

    addResult(
        'TC-B6-15',
        'Giữ chỗ làm giảm số ghế còn lại',
        'remaining giảm đúng 1',
        'before=' . $remainingBefore .
        '; after=' . $remainingAfterHold,
        $ok,
        'registration=' . $testRegistrationId
    );
}

/*
 * TC-16 expired hold releases seats
 */
if ($testRegistrationId) {

    App\Models\ClassRegistration::query()
        ->whereKey($testRegistrationId)
        ->update([
            'hold_expires_at' => now()->subMinute(),
        ]);

    $remainingAfterExpiry = $service->remainingSeats(
        $admin,
        $pairClass->id,
        $pairSchedule->id
    );

    $reg = App\Models\ClassRegistration::findOrFail(
        $testRegistrationId
    );

    $ok =
        $reg->status === 'cancelled' &&
        $remainingAfterExpiry === $remainingBefore;

    addResult(
        'TC-B6-16',
        'Hết hạn giữ chỗ tự trả ghế',
        'pending -> cancelled và ghế được trả lại',
        'status=' . $reg->status .
        '; remaining=' . $remainingAfterExpiry,
        $ok,
        'registration=' . $testRegistrationId
    );
}

/*
 * TC-17 re-register after expired
 */
try {

    $registration2 = $service->register(
        $admin,
        $pairClass->id,
        $pairSchedule->id,
        1
    );

    $testRegistrationId2 = $registration2->id;

    addResult(
        'TC-B6-17',
        'Đăng ký lại sau khi hold hết hạn',
        'Cho phép đăng ký lại',
        'id=' . $registration2->id .
        '; status=' . $registration2->status,
        $registration2->status === 'pending',
        'same class/schedule'
    );
} catch (Throwable $e) {
    addResult(
        'TC-B6-17',
        'Đăng ký lại sau khi hold hết hạn',
        'Tạo pending mới',
        $e->getMessage(),
        false
    );
}

/*
 * TC-18 duplicate active registration
 */
try {

    $service->register(
        $admin,
        $pairClass->id,
        $pairSchedule->id,
        1
    );

    addResult(
        'TC-B6-18',
        'Chặn đăng ký trùng',
        'Lần thứ hai phải bị từ chối',
        'Không phát sinh exception',
        false,
        'same class/schedule'
    );

} catch (RuntimeException $e) {

    addResult(
        'TC-B6-18',
        'Chặn đăng ký trùng',
        'Lần thứ hai bị từ chối',
        $e->getMessage(),
        true,
        'same class/schedule'
    );
}

/*
 * Cleanup active registration before concurrency.
 */
if ($testRegistrationId2) {
    App\Models\ClassRegistration::query()
        ->whereKey($testRegistrationId2)
        ->update([
            'status' => 'cancelled',
        ]);
}

/*
 * TC-19 concurrent final seat
 */
$concurrencyEvidence = [];

$concurrencySchedule = null;
$concurrencyClassA = null;
$concurrencyClassB = null;

foreach ($schedules as $schedule) {

    $candidates = [];

    foreach ($classes as $class) {

        if ($class->students()->count() < 1) {
            continue;
        }

        $active =
            App\Models\ClassRegistration::query()
                ->where('class_id', $class->id)
                ->where('schedule_id', $schedule->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

        if ($active) {
            continue;
        }

        $candidates[] = $class;
    }

    if (count($candidates) >= 2) {

        $remaining =
            $service->remainingSeats(
                $admin,
                $candidates[0]->id,
                $schedule->id
            );

        if ($remaining >= 1) {

            $concurrencySchedule = $schedule;
            $concurrencyClassA = $candidates[0];
            $concurrencyClassB = $candidates[1];

            break;
        }
    }
}

if (
    $concurrencySchedule &&
    $concurrencyClassA &&
    $concurrencyClassB
) {

    $activeUsed =
        App\Models\ClassRegistration::query()
            ->where('schedule_id', $concurrencySchedule->id)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('student_count');

    $originalCapacity =
        (int)$concurrencySchedule->capacity;

    $finalSeatCapacity = (int)$activeUsed + 1;

    if ($finalSeatCapacity < 1) {
        $finalSeatCapacity = 1;
    }

    App\Models\ProgramSchedule::query()
        ->whereKey($concurrencySchedule->id)
        ->update([
            'capacity' => $finalSeatCapacity,
        ]);

    $workerPath = __DIR__ . '/b6-concurrent-worker.php';

    $cmdA =
        escapeshellarg(PHP_BINARY) . ' ' .
        escapeshellarg($workerPath) . ' ' .
        (int)$admin->id . ' ' .
        (int)$concurrencyClassA->id . ' ' .
        (int)$concurrencySchedule->id . ' 1';

    $cmdB =
        escapeshellarg(PHP_BINARY) . ' ' .
        escapeshellarg($workerPath) . ' ' .
        (int)$admin->id . ' ' .
        (int)$concurrencyClassB->id . ' ' .
        (int)$concurrencySchedule->id . ' 1';

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $pipesA = [];
    $pipesB = [];

    $procA = proc_open(
        $cmdA,
        $descriptors,
        $pipesA,
        dirname(__DIR__)
    );

    $procB = proc_open(
        $cmdB,
        $descriptors,
        $pipesB,
        dirname(__DIR__)
    );

    $outA = '';
    $errA = '';
    $outB = '';
    $errB = '';

    if (is_resource($procA)) {
        $outA = stream_get_contents($pipesA[1]);
        $errA = stream_get_contents($pipesA[2]);
        fclose($pipesA[0]);
        fclose($pipesA[1]);
        fclose($pipesA[2]);
        proc_close($procA);
    }

    if (is_resource($procB)) {
        $outB = stream_get_contents($pipesB[1]);
        $errB = stream_get_contents($pipesB[2]);
        fclose($pipesB[0]);
        fclose($pipesB[1]);
        fclose($pipesB[2]);
        proc_close($procB);
    }

    $jsonA = json_decode(trim($outA), true);
    $jsonB = json_decode(trim($outB), true);

    $successCount =
        (($jsonA['ok'] ?? false) ? 1 : 0) +
        (($jsonB['ok'] ?? false) ? 1 : 0);

    $passedConcurrency = $successCount === 1;

    $concurrencyEvidence = [
        'schedule_id' => $concurrencySchedule->id,
        'class_a' => $concurrencyClassA->id,
        'class_b' => $concurrencyClassB->id,
        'final_capacity' => $finalSeatCapacity,
        'worker_a' => $jsonA,
        'worker_b' => $jsonB,
        'stderr_a' => $errA,
        'stderr_b' => $errB,
    ];

    addResult(
        'TC-B6-19',
        'Tranh chấp 1 ghế cuối',
        '2 tiến trình đồng thời: đúng 1 thành công',
        'success_count=' . $successCount,
        $passedConcurrency,
        json_encode(
            $concurrencyEvidence,
            JSON_UNESCAPED_UNICODE
        )
    );

    $cleanupIds =
        App\Models\ClassRegistration::query()
            ->where('schedule_id', $concurrencySchedule->id)
            ->whereIn('class_id', [
                $concurrencyClassA->id,
                $concurrencyClassB->id,
            ])
            ->where('status', 'pending')
            ->pluck('id');

    if ($cleanupIds->count() > 0) {
        App\Models\ClassRegistration::query()
            ->whereIn('id', $cleanupIds->all())
            ->update([
                'status' => 'cancelled',
            ]);
    }

    App\Models\ProgramSchedule::query()
        ->whereKey($concurrencySchedule->id)
        ->update([
            'capacity' => $originalCapacity,
        ]);
} else {

    addResult(
        'TC-B6-19',
        'Tranh chấp 1 ghế cuối',
        '2 tiến trình đồng thời: đúng 1 thành công',
        'Khong tim du cap class/schedule',
        false
    );
}

/*
 * TC-20 SQL injection
 */
try {

    $payload = "' OR 1=1 --";

    $response = Illuminate\Support\Facades\Http::timeout(10)
        ->get($baseUrl . '/api/v1/programs', [
            'keyword' => $payload,
            'per_page' => 5,
        ]);

    $body = $response->body();

    $dangerText =
        stripos($body, 'SQLSTATE') !== false ||
        stripos($body, 'syntax error') !== false ||
        stripos($body, 'mysql') !== false;

    $ok =
        $response->status() === 200 &&
        !$dangerText;

    addResult(
        'TC-B6-20',
        'Kiểm thử SQL Injection ở ô tìm kiếm',
        'Không lỗi SQL / không HTTP 500',
        'HTTP ' . $response->status(),
        $ok,
        "keyword=$payload"
    );
} catch (Throwable $e) {

    addResult(
        'TC-B6-20',
        'Kiểm thử SQL Injection ở ô tìm kiếm',
        'Không lỗi SQL',
        $e->getMessage(),
        false
    );
}

/*
 * Cleanup registration from TC13 if still exists.
 */
if ($testRegistrationId) {
    App\Models\ClassRegistration::query()
        ->whereKey($testRegistrationId)
        ->whereIn('status', ['pending', 'approved'])
        ->update([
            'status' => 'cancelled',
        ]);
}

$passed = count(
    array_filter(
        $results,
        fn ($r) => $r['result'] === 'PASS'
    )
);

$failed = count($results) - $passed;

@mkdir(
    dirname(__DIR__, 2) . '/docs/evidence/buoi-6',
    0777,
    true
);

$file = dirname(__DIR__, 2) . '/docs/evidence/buoi-6/20-test-results.json';

file_put_contents(
    $file,
    json_encode(
        $results,
        JSON_UNESCAPED_UNICODE |
        JSON_PRETTY_PRINT
    )
);

$summaryFile =
    dirname(__DIR__, 2) .
    '/docs/evidence/buoi-6/concurrent-booking-evidence.json';

file_put_contents(
    $summaryFile,
    json_encode(
        [
            'executed_at' => date('Y-m-d H:i:s'),
            'pair_class' => $pairClass->id,
            'pair_schedule' => $pairSchedule->id,
            'results' => $concurrencyEvidence,
        ],
        JSON_UNESCAPED_UNICODE |
        JSON_PRETTY_PRINT
    )
);

echo PHP_EOL;
echo "================ B6 TEST SUMMARY ================" . PHP_EOL;
echo "TOTAL : " . count($results) . PHP_EOL;
echo "PASS  : " . $passed . PHP_EOL;
echo "FAIL  : " . $failed . PHP_EOL;
echo "JSON  : " . $file . PHP_EOL;
echo "==================================================" . PHP_EOL;

foreach ($results as $row) {
    echo
        $row['id'] . ' | ' .
        $row['result'] . ' | ' .
        $row['name'] . PHP_EOL;
}

if ($failed > 0) {
    exit(2);
}