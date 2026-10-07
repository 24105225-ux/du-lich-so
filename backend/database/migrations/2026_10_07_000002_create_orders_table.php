<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            return;
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('class_registration_id')
                ->constrained('class_registrations')
                ->cascadeOnDelete();

            $table->string('order_no', 40)->unique();

            $table->decimal('amount', 12, 2)->default(0);

            $table->enum('status', [
                'pending',
                'paid',
                'cancelled',
                'refunded',
            ])->default('pending');

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();

            $table->index(
                ['user_id', 'status'],
                'idx_orders_user_status'
            );

            $table->index(
                ['status', 'created_at'],
                'idx_orders_status_created'
            );

            $table->index(
                'class_registration_id',
                'idx_orders_registration'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};