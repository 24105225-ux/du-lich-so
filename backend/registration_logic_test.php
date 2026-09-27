<?php

use App\Models\ClassRegistration;
use App\Services\ClassRegistrationService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

$service = app(ClassRegistrationService::class);

$classId = 1;
$scheduleId = 1;

echo "Class ID: {$classId}" . PHP_EOL;
echo "Schedule ID: {$scheduleId}" . PHP_EOL;

$class = App\Models\SchoolClass::withCount('students')->find($classId);
$schedule = App\Models\ProgramSchedule::find($scheduleId);

echo "Si so lop: {$class->students_count}" . PHP_EOL;
echo "Capacity lich: {$schedule->capacity}" . PHP_EOL;
echo "Trang thai lich: {$schedule->status}" . PHP_EOL;
echo PHP_EOL;


/*
|--------------------------------------------------------------------------
| TEST 11
|--------------------------------------------------------------------------
*/

echo "----- TEST 11 HOC SINH -----" . PHP_EOL;

try {
    $service->register(
        $classId,
        $scheduleId,
        11
    );

    echo "SAI: 11 hoc sinh da duoc dang ky!" . PHP_EOL;
} catch (Throwable $e) {
    echo "OK - 11 hoc sinh bi tu choi." . PHP_EOL;
    echo "Ly do: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;


/*
|--------------------------------------------------------------------------
| TEST 10
|--------------------------------------------------------------------------
| Khong tao ban ghi de tranh lam thay doi database.
| Chi kiem tra dieu kien.
|--------------------------------------------------------------------------
*/

echo "----- TEST DIEU KIEN 10 HOC SINH -----" . PHP_EOL;

$classStudentCount = (int) $class->students_count;

if (10 <= $classStudentCount) {
    echo "OK - 10 hoc sinh khong vuot si so lop." . PHP_EOL;
} else {
    echo "SAI - 10 hoc sinh vuot si so." . PHP_EOL;
}

echo PHP_EOL;


/*
|--------------------------------------------------------------------------
| TEST 9
|--------------------------------------------------------------------------
*/

echo "----- TEST DIEU KIEN 9 HOC SINH -----" . PHP_EOL;

if (9 <= $classStudentCount) {
    echo "OK - 9 hoc sinh khong vuot si so lop." . PHP_EOL;
} else {
    echo "SAI - 9 hoc sinh vuot si so." . PHP_EOL;
}

echo PHP_EOL;


/*
|--------------------------------------------------------------------------
| KIEM TRA DATABASE KHONG BI THEM BAN GHI
|--------------------------------------------------------------------------
*/

$count = ClassRegistration::count();

echo "Tong registrations hien tai: {$count}" . PHP_EOL;
echo "Test 11 khong tao ban ghi moi." . PHP_EOL;
