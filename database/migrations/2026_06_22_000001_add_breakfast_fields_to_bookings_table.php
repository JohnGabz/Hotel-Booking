<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('with_breakfast')->default(false)->after('total');
            $table->decimal('breakfast_charge', 10, 2)->default(0.00)->after('with_breakfast');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['with_breakfast', 'breakfast_charge']);
        });
    }
};
