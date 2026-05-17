<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['room_id', 'status', 'check_in', 'check_out'], 'bookings_room_status_dates_idx');
            $table->index(['payment_status', 'paid_at'], 'bookings_payment_status_paid_at_idx');
            $table->index('payment_reference', 'bookings_payment_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_room_status_dates_idx');
            $table->dropIndex('bookings_payment_status_paid_at_idx');
            $table->dropIndex('bookings_payment_reference_idx');
        });
    }
};
