<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->smallInteger('with_breakfast')->default(0)->change();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->smallInteger('approved')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('with_breakfast')->default(false)->change();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('approved')->default(false)->change();
        });
    }
};