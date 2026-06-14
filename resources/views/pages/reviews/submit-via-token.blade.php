@extends('layouts.site')

@section('content')
<section class="relative overflow-hidden py-16 sm:py-24">
    <div class="site-shell relative z-10">
        <div class="mx-auto max-w-2xl">
            @if ($submitted)
                <!-- Thank You Screen -->
                <div class="rounded-3xl border border-stone-200 bg-white p-8 shadow-xl sm:p-12 text-center space-y-6">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    <div class="space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-primary">Thank You!</p>
                        <h1 class="text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">Review Submitted</h1>
                        <p class="text-sm leading-relaxed text-stone-600">
                            We appreciate your feedback for booking #{{ $booking->id }} at <strong>{{ $booking->room?->name }}</strong>. 
                            Your review helps us maintain our high standard of hospitality. It will be published on our site once approved by our team.
                        </p>
                    </div>

                    <div class="pt-6">
                        <a href="{{ route('home') }}" class="btn-primary inline-flex justify-center w-full sm:w-auto">Return to Home</a>
                    </div>
                </div>
            @else
                <!-- Review Form -->
                <div class="rounded-3xl border border-stone-200 bg-white p-8 shadow-xl sm:p-12 space-y-8">
                    <div class="space-y-3">
                        <span class="eyebrow">Guest Review Invitation</span>
                        <h1 class="text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">Share Your Experience</h1>
                        <p class="text-sm leading-relaxed text-stone-600">
                            Tell us about your stay in the <strong>{{ $booking->room?->name }}</strong> (Stay dates: {{ $booking->check_in->format('M d, Y') }} - {{ $booking->check_out->format('M d, Y') }}). Your feedback is highly valuable to us.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('reviews.submit-via-token.store', $token) }}" class="space-y-6">
                        @csrf

                        <!-- Interactive Stars Rating -->
                        <div class="space-y-2">
                            <label class="form-label font-semibold">Your Rating</label>
                            <div class="flex items-center gap-2 pt-1" id="star-rating-container">
                                @for ($i = 1; $i <= 5; $i++)
                                    <button type="button" data-star="{{ $i }}" class="text-stone-300 hover:text-amber-500 hover:scale-110 transition focus:outline-none" aria-label="Rate {{ $i }} star{{ $i > 1 ? 's' : '' }}">
                                        <svg class="h-10 w-10 fill-current" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </button>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="rating-input" value="{{ old('rating') }}" required>
                            @error('rating')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Comment Textarea -->
                        <div class="space-y-2">
                            <label for="comment" class="form-label font-semibold">Your Review / Comments</label>
                            <textarea id="comment" name="comment" rows="5" class="form-input w-full rounded-xl @error('comment') border-red-500 @enderror" placeholder="Write about your stay (minimum 10 characters)..." required>{{ old('comment') }}</textarea>
                            <p class="text-[11px] text-stone-500">Provide details on what you liked or how we can improve. (10 - 1000 characters)</p>
                            @error('comment')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4 border-t border-stone-200 flex items-center justify-between gap-4">
                            <a href="{{ route('home') }}" class="btn-secondary">Cancel</a>
                            <button type="submit" class="btn-primary">Submit Review</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const stars = document.querySelectorAll('[data-star]');
        const ratingInput = document.getElementById('rating-input');

        const updateStars = (rating) => {
            stars.forEach(star => {
                const starValue = parseInt(star.getAttribute('data-star'), 10);
                if (starValue <= rating) {
                    star.classList.remove('text-stone-300');
                    star.classList.add('text-amber-500');
                } else {
                    star.classList.remove('text-amber-500');
                    star.classList.add('text-stone-300');
                }
            });
        };

        stars.forEach(star => {
            star.addEventListener('click', () => {
                const ratingValue = parseInt(star.getAttribute('data-star'), 10);
                ratingInput.value = ratingValue;
                updateStars(ratingValue);
            });

            star.addEventListener('mouseover', () => {
                const hoverValue = parseInt(star.getAttribute('data-star'), 10);
                updateStars(hoverValue);
            });

            star.addEventListener('mouseout', () => {
                const currentValue = parseInt(ratingInput.value, 10) || 0;
                updateStars(currentValue);
            });
        });

        // Initialize from old input if validation failed
        const initialRating = parseInt(ratingInput.value, 10) || 0;
        if (initialRating > 0) {
            updateStars(initialRating);
        }
    });
</script>
@endpush
@endsection
