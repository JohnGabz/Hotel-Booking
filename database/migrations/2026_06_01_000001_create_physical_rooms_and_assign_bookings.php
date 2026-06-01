<?php

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('status')->default('available');
            $table->timestamps();

            $table->index(['room_id', 'status']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'physical_room_id')) {
                $table->foreignId('physical_room_id')->nullable()->after('room_id')->constrained('physical_rooms')->nullOnDelete();
                $table->index(['physical_room_id', 'status', 'check_in', 'check_out'], 'bookings_physical_room_status_dates_idx');
            }
        });

        Room::query()->orderBy('id')->each(function (Room $room): void {
            $physicalRoomId = DB::table('physical_rooms')->insertGetId([
                'room_id' => $room->id,
                'name' => $room->name . ' 1',
                'code' => Str::slug($room->name) . '-1',
                'status' => $room->status === 'maintenance' ? 'maintenance' : 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Booking::query()
                ->where('room_id', $room->id)
                ->whereNull('physical_room_id')
                ->update(['physical_room_id' => $physicalRoomId]);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'physical_room_id')) {
                $table->dropConstrainedForeignId('physical_room_id');
            }
        });

        Schema::dropIfExists('physical_rooms');
    }
};
