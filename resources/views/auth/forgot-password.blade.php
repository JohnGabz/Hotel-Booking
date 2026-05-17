@extends('layouts.site')

@section('content')
<section class="section-shell bg-white">
    <div class="site-shell">
        <div class="max-w-2xl mx-auto">
            <div class="card">
                <div class="mb-8 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-brand-primary">Account recovery</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900">Forgot password</h1>
                </div>
                <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" name="email" type="email" class="form-input" value="{{ old('email') }}" autocomplete="email" required autofocus>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn-primary w-full">Send password reset link</button>
                </form>
                <p class="mt-6 text-center text-sm text-slate-600">Remembered your password? <a href="{{ route('login') }}" class="font-semibold text-brand-primary">Back to login</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
