@extends('layouts.site')

@section('content')
<section class="section-shell bg-white">
    <div class="site-shell">
        <div class="max-w-2xl mx-auto">
            <div class="card">
                <div class="mb-8 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-brand-primary">Create account</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900">Register for Villa Estella</h1>
                    <p class="mt-3 text-sm text-slate-600">Email verification is required before booking and reviewing rooms.</p>
                </div>
                <form action="{{ route('register') }}" method="POST" class="space-y-5">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="name">Name</label>
                        <input id="name" name="name" class="form-input" value="{{ old('name') }}" autocomplete="name" required autofocus>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" name="email" type="email" class="form-input" value="{{ old('email') }}" autocomplete="email" required>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="contact_number">Contact Number</label>
                        <input id="contact_number" name="contact_number" type="tel" class="form-input" value="{{ old('contact_number') }}" required placeholder="e.g., +63 912 345 6789">
                        @error('contact_number') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input id="password" name="password" type="password" class="form-input" autocomplete="new-password" required>
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-input" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn-primary w-full">Register</button>
                </form>
                <p class="mt-6 text-center text-sm text-slate-600">Already registered? <a href="{{ route('login') }}" class="font-semibold text-brand-primary">Login here</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
