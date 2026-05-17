@extends('layouts.site')

@section('content')
<section class="section-shell bg-stone-50">
    <div class="site-shell">
        <div class="max-w-2xl mx-auto">
            <div class="card">
                <div class="mb-8 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-brand-primary">Secure access</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900">Reset password</h1>
                </div>
                <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" name="email" type="email" class="form-input" value="{{ old('email', $email) }}" autocomplete="email" required>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">New password</label>
                        <input id="password" name="password" type="password" class="form-input" autocomplete="new-password" required>
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm new password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-input" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn-primary w-full">Update password</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
