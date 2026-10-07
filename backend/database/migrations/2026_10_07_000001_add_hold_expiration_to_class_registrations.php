<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('class_registrations', 'hold_expires_at')) {
            Schema::table('class_registrations', function (Blueprint $table) {
                $table->timestamp('hold_expires_at')
                    ->nullable()
                    ->after('status');

                $table->index(
                    'hold_expires_at',
                    'idx_reg_hold_expires'
                );
            });
        }

        $indexes = DB::select('SHOW INDEX FROM class_registrations');

        $hasHoldIndex = false;

        foreach ($indexes as $index) {
            if ($index->Key_name === 'idx_reg_hold_expires') {
                $hasHoldIndex = true;
                break;
            }
        }

        if (!$hasHoldIndex && Schema::hasColumn('class_registrations', 'hold_expires_at')) {
            Schema::table('class_registrations', function (Blueprint $table) {
                $table->index(
                    'hold_expires_at',
                    'idx_reg_hold_expires'
                );
            });
        }

        DB::table('class_registrations')
            ->where('status', 'pending')
            ->whereNull('hold_expires_at')
            ->update([
                'hold_expires_at' => DB::raw(
                    'DATE_ADD(COALESCE(registered_at, CURRENT_TIMESTAMP), INTERVAL 15 MINUTE)'
                ),
            ]);
    }

    public function down(): void
    {
        $indexes = DB::select('SHOW INDEX FROM class_registrations');

        $hasHoldIndex = false;

        foreach ($indexes as $index) {
            if ($index->Key_name === 'idx_reg_hold_expires') {
                $hasHoldIndex = true;
                break;
            }
        }

        if ($hasHoldIndex) {
            Schema::table('class_registrations', function (Blueprint $table) {
                $table->dropIndex('idx_reg_hold_expires');
            });
        }

        if (Schema::hasColumn('class_registrations', 'hold_expires_at')) {
            Schema::table('class_registrations', function (Blueprint $table) {
                $table->dropColumn('hold_expires_at');
            });
        }
    }
};