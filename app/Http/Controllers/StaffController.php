<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffController extends Controller
{
    protected function ensureStaff(): void
    {
        if (! Auth::check() || ! Auth::user()?->is_admin) {
            abort(403);
        }
    }

    public function dashboard(Request $request): View
    {
        $this->ensureStaff();

        if ($request->ajax() && $request->query('section')) {
            return $this->section($request);
        }

        return view('staff.dashboard', [
            'activeSection' => $request->query('section', 'bookings'),
            'seo' => [
                'title' => 'Staff Dashboard — ' . config('app.name'),
            ],
        ]);
    }

    public function section(Request $request): View
    {
        $this->ensureStaff();
        $section = $request->query('section', 'bookings');

        $data = match ($section) {
            'guests' => [
                'users' => User::with(['bookings.room'])->latest()->get(),
            ],
            default => [
                'bookings' => Booking::with(['room', 'physicalRoom', 'user'])->latest()->get(),
            ],
        };

        return view('staff.sections.' . $section, $data);
    }
}
