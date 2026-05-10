<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE bookings ALTER COLUMN contact_name DROP NOT NULL");
            DB::statement("ALTER TABLE bookings ALTER COLUMN contact_email DROP NOT NULL");
            DB::statement("ALTER TABLE bookings ALTER COLUMN contact_phone DROP NOT NULL");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY contact_name VARCHAR(255) NULL");
            DB::statement("ALTER TABLE bookings MODIFY contact_email VARCHAR(255) NULL");
            DB::statement("ALTER TABLE bookings MODIFY contact_phone VARCHAR(80) NULL");
        } else {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('contact_name')->nullable()->change();
                $table->string('contact_email')->nullable()->change();
                $table->string('contact_phone', 80)->nullable()->change();
            });
        }

        // Backfill any existing NULLs with defaults to be safe
        DB::table('bookings')->whereNull('contact_name')->update(['contact_name' => 'Guest']);
        DB::table('bookings')->whereNull('contact_email')->update(['contact_email' => 'guest@villaestella.test']);
        DB::table('bookings')->whereNull('contact_phone')->update(['contact_phone' => '+63 900 000 0000']);
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE bookings ALTER COLUMN contact_name SET NOT NULL");
            DB::statement("ALTER TABLE bookings ALTER COLUMN contact_email SET NOT NULL");
            DB::statement("ALTER TABLE bookings ALTER COLUMN contact_phone SET NOT NULL");
        } elseif ($driver === 'mysql') {
            DB::statement("ALTER TABLE bookings MODIFY contact_name VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE bookings MODIFY contact_email VARCHAR(255) NOT NULL");
            DB::statement("ALTER TABLE bookings MODIFY contact_phone VARCHAR(80) NOT NULL");
        } else {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('contact_name')->nullable(false)->change();
                $table->string('contact_email')->nullable(false)->change();
                $table->string('contact_phone', 80)->nullable(false)->change();
            });
        }
    }
};
