<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(Request $request): View
    {
        if ($request->has('check_in')) {
            session(['pending_booking' => $request->only('room_id', 'check_in', 'check_out', 'guests', 'booking_type')]);
        }

        return view('auth.login', [
            'seo' => [
                'title' => 'Login — ' . config('app.name'),
                'description' => 'Sign in to manage your Villa Estella reservations.',
            ],
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (! Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            return back()->withErrors(['email' => 'The provided credentials do not match our records.'])->withInput();
        }

        $request->session()->regenerate();

        /** @var User|null $user */
        $user = Auth::user();

        if ($user && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if (session()->has('pending_booking')) {
            $pendingBooking = session()->get('pending_booking');
            $room = \App\Models\Room::find($pendingBooking['room_id']);
            if ($room) {
                session()->forget('pending_booking');
                return redirect()->route('rooms.show', [
                    'room' => $room->slug,
                    'check_in' => $pendingBooking['check_in'],
                    'check_out' => $pendingBooking['check_out'],
                    'guests' => $pendingBooking['guests'] ?? 1,
                    'booking_type' => $pendingBooking['booking_type'] ?? 'booking',
                    'booking_modal' => 1
                ]);
            }
        }

        return redirect()->intended(route('dashboard'));
    }

    public function showRegisterForm(Request $request): View
    {
        if ($request->has('check_in')) {
            session(['pending_booking' => $request->only('room_id', 'check_in', 'check_out', 'guests', 'booking_type')]);
        }

        return view('auth.register', [
            'seo' => [
                'title' => 'Register — ' . config('app.name'),
                'description' => 'Create your guest account for Villa Estella.',
            ],
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'contact_number' => ['required', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:7', 'max:20'],
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        event(new Registered($user));

        return redirect()->route('verification.notice')->with('success', 'Your account was created. Please verify your email to continue.');
    }

    public function showVerifyEmailNotice(): View|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email', [
            'seo' => [
                'title' => 'Verify Email - ' . config('app.name'),
                'description' => 'Verify your email address to access booking and dashboard features.',
            ],
        ]);
    }

    public function resendVerificationEmail(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $user?->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent. Please check your email inbox.');
    }

    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password', [
            'seo' => [
                'title' => 'Forgot Password - ' . config('app.name'),
                'description' => 'Request a password reset email for your Villa Estella account.',
            ],
        ]);
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
            'seo' => [
                'title' => 'Reset Password - ' . config('app.name'),
                'description' => 'Set a new password for your Villa Estella account.',
            ],
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request): void {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')->toString()),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', __($status));
        }

        return back()->withErrors(['email' => [__($status)]]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
