@extends('layouts.site')

@section('content')
<section class="py-24 bg-slate-50">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <div class="card">
                <div class="mb-8 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-sky-600">Welcome back</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900">Sign in to your account</h1>
                </div>
                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" name="email" type="email" class="form-input" value="{{ old('email') }}" required autofocus>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input id="password" name="password" type="password" class="form-input" required>
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center justify-between gap-4 text-sm">
                        <label class="inline-flex items-center gap-2 text-slate-600">
                            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                            <span>Remember me</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="font-semibold text-sky-600">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn-primary w-full">Login</button>
                </form>
                <p class="mt-6 text-center text-sm text-slate-600">New here? <a href="{{ route('register') }}" class="text-sky-600 font-semibold">Create an account</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
