@extends('layouts.site')

@section('content')
<section class="py-24 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <div class="card">
                <div class="mb-8 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-sky-600">Create account</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900">Register for Villa Estella</h1>
                    <p class="mt-3 text-sm text-slate-600">Email verification is required before booking and reviewing rooms.</p>
                </div>
                <form action="{{ route('register') }}" method="POST" class="space-y-5">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="name">Name</label>
                        <input id="name" name="name" class="form-input" value="{{ old('name') }}" required autofocus>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" name="email" type="email" class="form-input" value="{{ old('email') }}" required>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input id="password" name="password" type="password" class="form-input" required>
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-input" required>
                    </div>
                    <button type="submit" class="btn-primary w-full">Register</button>
                </form>
                <p class="mt-6 text-center text-sm text-slate-600">Already registered? <a href="{{ route('login') }}" class="text-sky-600 font-semibold">Login here</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
