<?php

namespace App\Http\Controllers;

use App\Models\PhysicalRoom;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PhysicalRoomController extends Controller
{
    protected function ensureAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()?->is_admin) {
            abort(403);
        }
    }

    public function store(Request $request, Room $room): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('physical_rooms', 'code')],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $room->physicalRooms()->create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'status' => $request->boolean('is_available', true) ? 'available' : 'maintenance',
        ]);

        return redirect()->route('admin.rooms')
            ->with('success', 'Physical room added successfully.');
    }

    public function destroy(PhysicalRoom $physicalRoom): RedirectResponse
    {
        $this->ensureAdmin();

        if ($physicalRoom->bookings()->exists()) {
            return redirect()->route('admin.rooms')
                ->with('error', 'Cannot delete a physical room that has bookings.');
        }

        $physicalRoom->delete();

        return redirect()->route('admin.rooms')
            ->with('success', 'Physical room removed successfully.');
    }
}
