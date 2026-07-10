<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('booking_type')->default('booking')->after('status');
            $table->decimal('amount_paid', 10, 2)->default(0.00)->after('payment_status');
            $table->decimal('cancellation_penalty', 10, 2)->default(0.00)->after('amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_type', 'amount_paid', 'cancellation_penalty']);
        });
    }
};
