<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->string('payment_proof_path')->nullable()->after('payment_reference');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_reference', 'payment_proof_path', 'paid_at']);
        });
    }
};
