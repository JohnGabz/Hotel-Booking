@extends('layouts.site')

@section('content')
<section class="section-shell bg-stone-50">
    <div class="site-shell">
        <div class="max-w-2xl mx-auto">
            <div class="card">
                <div class="mb-8 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-brand-primary">One more step</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900">Verify your email</h1>
                    <p class="mt-4 text-sm text-slate-600">Please check your inbox and click the verification link before booking rooms or leaving reviews.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
                        <p class="text-sm text-red-700 font-semibold">Verification link error</p>
                        <p class="text-sm text-red-600 mt-2">{{ $errors->first() ?? 'The verification link is invalid or has expired.' }}</p>
                    </div>
                @endif

                <form action="{{ route('verification.send') }}" method="POST" class="space-y-4">
                    @csrf
                    <button type="submit" class="btn-primary w-full">Resend verification email</button>
                </form>

                <div class="mt-6 text-center text-sm text-slate-600">
                    <p>Wrong account?</p>
                    <form action="{{ route('logout') }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="font-semibold text-brand-primary">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
