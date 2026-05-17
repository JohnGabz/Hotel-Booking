@extends('layouts.site')

@section('content')
<section class="section-shell pt-8 sm:pt-10">
    <div class="site-shell grid gap-8 lg:grid-cols-[0.95fr_1.05fr] lg:items-start">
        <div class="space-y-5">
            <span class="eyebrow">Contact us</span>
            <h1 class="responsive-title">Need help with a booking?</h1>
            <p class="max-w-xl text-base leading-7 text-stone-600 sm:text-lg">
                Reach out for availability questions, guest support, or anything else you need before your stay.
            </p>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="card">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Email</p>
                    <p class="mt-3 text-lg font-semibold text-stone-950">{{ $contactInfo['email'] }}</p>
                </div>
                <div class="card">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Phone</p>
                    <p class="mt-3 text-lg font-semibold text-stone-950">{{ $contactInfo['phone'] }}</p>
                </div>
                <div class="card sm:col-span-2">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Address</p>
                    <p class="mt-3 text-lg font-semibold text-stone-950">{{ $contactInfo['address'] }}</p>
                </div>
            </div>
        </div>

        <div class="surface p-6 sm:p-8">
            <span class="eyebrow">Send a message</span>
            <form action="{{ route('contact.submit') }}" method="POST" class="mt-6 space-y-4">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="name">Name</label>
                    <input id="name" name="name" class="form-input" value="{{ old('name') }}" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" name="email" type="email" class="form-input" value="{{ old('email') }}" required>
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="message">Message</label>
                    <textarea id="message" name="message" rows="6" class="form-input" required>{{ old('message') }}</textarea>
                    @error('message') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary w-full">Send message</button>
            </form>
        </div>
    </div>
</section>
@endsection
