<?php

namespace Database\Seeders;

use App\Models\PhysicalRoom;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PhysicalRoomSeeder extends Seeder
{
    public function run(): void
    {
        Room::query()->orderBy('id')->each(function (Room $room): void {
            if ($room->physicalRooms()->exists()) {
                return;
            }

            PhysicalRoom::query()->create([
                'room_id' => $room->id,
                'name' => $room->name . ' 1',
                'code' => Str::slug($room->slug) . '-1',
                'status' => $room->status === 'maintenance' ? 'maintenance' : 'available',
            ]);
        });
    }
}
