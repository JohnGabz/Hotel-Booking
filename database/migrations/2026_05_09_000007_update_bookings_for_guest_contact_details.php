<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'contact_name')) {
                $table->string('contact_name')->nullable()->after('guests');
            }

            if (! Schema::hasColumn('bookings', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('contact_name');
            }

            if (! Schema::hasColumn('bookings', 'contact_phone')) {
                $table->string('contact_phone', 80)->nullable()->after('contact_email');
            }
        });

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE bookings DROP FOREIGN KEY bookings_user_id_foreign');
            DB::statement('ALTER TABLE bookings MODIFY user_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_user_id_foreign');
            DB::statement('ALTER TABLE bookings ALTER COLUMN user_id TYPE bigint');
            DB::statement('ALTER TABLE bookings ALTER COLUMN user_id DROP NOT NULL');
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        } else {
            Schema::table('bookings', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        DB::table('bookings')
            ->whereNull('contact_name')
            ->update([
                'contact_name' => DB::raw("COALESCE(contact_name, 'Guest')"),
                'contact_email' => DB::raw("COALESCE(contact_email, 'guest@villaestella.test')"),
                'contact_phone' => DB::raw("COALESCE(contact_phone, '+63 900 000 0000')"),
            ]);
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE bookings DROP FOREIGN KEY bookings_user_id_foreign');
            DB::statement('ALTER TABLE bookings MODIFY user_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_user_id_foreign');
            DB::statement('ALTER TABLE bookings ALTER COLUMN user_id TYPE bigint');
            DB::statement('ALTER TABLE bookings ALTER COLUMN user_id SET NOT NULL');
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        } else {
            Schema::table('bookings', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'contact_email', 'contact_phone']);
        });
    }
};
