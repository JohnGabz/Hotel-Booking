<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'pgsql') {
            return;
        }

        // Query the information schema to check if column type is boolean
        $columnType = DB::selectOne("
            SELECT data_type 
            FROM information_schema.columns 
            WHERE table_schema = 'public' 
              AND table_name = 'bookings' 
              AND column_name = 'with_breakfast'
        ")?->data_type ?? null;

        if ($columnType === 'boolean') {
            // Drop the boolean default first -- Postgres can't auto-cast
            // an existing `false`/`true` default to smallint.
            DB::statement('ALTER TABLE bookings ALTER COLUMN with_breakfast DROP DEFAULT');
            DB::statement('
                ALTER TABLE bookings
                ALTER COLUMN with_breakfast TYPE smallint
                USING (CASE WHEN with_breakfast THEN 1 ELSE 0 END)
            ');
            DB::statement('ALTER TABLE bookings ALTER COLUMN with_breakfast SET DEFAULT 0');
            DB::statement('ALTER TABLE bookings ALTER COLUMN with_breakfast SET NOT NULL');
        }

        $approvedType = DB::selectOne("
            SELECT data_type 
            FROM information_schema.columns 
            WHERE table_schema = 'public' 
              AND table_name = 'reviews' 
              AND column_name = 'approved'
        ")?->data_type ?? null;

        if ($approvedType === 'boolean') {
            DB::statement('ALTER TABLE reviews ALTER COLUMN approved DROP DEFAULT');
            DB::statement('
                ALTER TABLE reviews
                ALTER COLUMN approved TYPE smallint
                USING (CASE WHEN approved THEN 1 ELSE 0 END)
            ');
            DB::statement('ALTER TABLE reviews ALTER COLUMN approved SET DEFAULT 0');
            DB::statement('ALTER TABLE reviews ALTER COLUMN approved SET NOT NULL');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'pgsql') {
            return;
        }

        $columnType = DB::selectOne("
            SELECT data_type 
            FROM information_schema.columns 
            WHERE table_schema = 'public' 
              AND table_name = 'bookings' 
              AND column_name = 'with_breakfast'
        ")?->data_type ?? null;

        if ($columnType !== 'boolean' && $columnType !== null) {
            DB::statement('ALTER TABLE bookings ALTER COLUMN with_breakfast DROP DEFAULT');
            DB::statement('
                ALTER TABLE bookings
                ALTER COLUMN with_breakfast TYPE boolean
                USING (with_breakfast::int::boolean)
            ');
            DB::statement('ALTER TABLE bookings ALTER COLUMN with_breakfast SET DEFAULT false');
            DB::statement('ALTER TABLE bookings ALTER COLUMN with_breakfast SET NOT NULL');
        }

        $approvedType = DB::selectOne("
            SELECT data_type 
            FROM information_schema.columns 
            WHERE table_schema = 'public' 
              AND table_name = 'reviews' 
              AND column_name = 'approved'
        ")?->data_type ?? null;

        if ($approvedType !== 'boolean' && $approvedType !== null) {
            DB::statement('ALTER TABLE reviews ALTER COLUMN approved DROP DEFAULT');
            DB::statement('
                ALTER TABLE reviews
                ALTER COLUMN approved TYPE boolean
                USING (approved::int::boolean)
            ');
            DB::statement('ALTER TABLE reviews ALTER COLUMN approved SET DEFAULT false');
            DB::statement('ALTER TABLE reviews ALTER COLUMN approved SET NOT NULL');
        }
    }
};
