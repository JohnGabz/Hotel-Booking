@extends('layouts.site')

@section('content')
<section class="py-24 bg-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <div class="card">
                <div class="mb-8 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-sky-600">Account recovery</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900">Forgot password</h1>
                </div>
                <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" name="email" type="email" class="form-input" value="{{ old('email') }}" required autofocus>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn-primary w-full">Send password reset link</button>
                </form>
                <p class="mt-6 text-center text-sm text-slate-600">Remembered your password? <a href="{{ route('login') }}" class="text-sky-600 font-semibold">Back to login</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
