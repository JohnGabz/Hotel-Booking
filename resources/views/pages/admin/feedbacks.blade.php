@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <section class="surface p-6 sm:p-8 lg:p-10">
        <span class="eyebrow">Feedbacks & Testimonials</span>
        <h1 class="mt-4 responsive-title lg:text-5xl">Landing Page Testimonials</h1>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">
            Configure the three testimonial quotes displayed on the public landing page.
        </p>
    </section>

    <!-- Settings Form -->
    <section class="card bg-white p-6 sm:p-8 rounded-xl border border-stone-200 shadow-sm">
        <form action="{{ route('admin.feedbacks.testimonials.update') }}" method="POST" class="space-y-8">
            @csrf
            
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Testimonial 1 -->
                <div class="space-y-4 p-5 bg-stone-50 rounded-xl border border-stone-200/60">
                    <h3 class="text-base font-semibold text-stone-900 border-b border-stone-200 pb-2">Testimonial 1</h3>
                    
                    <div class="form-group">
                        <label class="form-label font-medium text-stone-700" for="testimonial_1_name">Author Name</label>
                        <input id="testimonial_1_name" name="testimonial_1_name" type="text" class="form-input w-full mt-1" value="{{ old('testimonial_1_name', $landingContent['testimonial_1_name'] ?? '') }}" required>
                        @error('testimonial_1_name') <p class="form-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label font-medium text-stone-700" for="testimonial_1_role">Author Role / Designation</label>
                        <input id="testimonial_1_role" name="testimonial_1_role" type="text" class="form-input w-full mt-1" value="{{ old('testimonial_1_role', $landingContent['testimonial_1_role'] ?? '') }}" required>
                        @error('testimonial_1_role') <p class="form-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label font-medium text-stone-700" for="testimonial_1_quote">Quote</label>
                        <textarea id="testimonial_1_quote" name="testimonial_1_quote" rows="4" class="form-input w-full mt-1" required>{{ old('testimonial_1_quote', $landingContent['testimonial_1_quote'] ?? '') }}</textarea>
                        @error('testimonial_1_quote') <p class="form-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="space-y-4 p-5 bg-stone-50 rounded-xl border border-stone-200/60">
                    <h3 class="text-base font-semibold text-stone-900 border-b border-stone-200 pb-2">Testimonial 2</h3>
                    
                    <div class="form-group">
                        <label class="form-label font-medium text-stone-700" for="testimonial_2_name">Author Name</label>
                        <input id="testimonial_2_name" name="testimonial_2_name" type="text" class="form-input w-full mt-1" value="{{ old('testimonial_2_name', $landingContent['testimonial_2_name'] ?? '') }}" required>
                        @error('testimonial_2_name') <p class="form-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label font-medium text-stone-700" for="testimonial_2_role">Author Role / Designation</label>
                        <input id="testimonial_2_role" name="testimonial_2_role" type="text" class="form-input w-full mt-1" value="{{ old('testimonial_2_role', $landingContent['testimonial_2_role'] ?? '') }}" required>
                        @error('testimonial_2_role') <p class="form-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label font-medium text-stone-700" for="testimonial_2_quote">Quote</label>
                        <textarea id="testimonial_2_quote" name="testimonial_2_quote" rows="4" class="form-input w-full mt-1" required>{{ old('testimonial_2_quote', $landingContent['testimonial_2_quote'] ?? '') }}</textarea>
                        @error('testimonial_2_quote') <p class="form-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="space-y-4 p-5 bg-stone-50 rounded-xl border border-stone-200/60">
                    <h3 class="text-base font-semibold text-stone-900 border-b border-stone-200 pb-2">Testimonial 3</h3>
                    
                    <div class="form-group">
                        <label class="form-label font-medium text-stone-700" for="testimonial_3_name">Author Name</label>
                        <input id="testimonial_3_name" name="testimonial_3_name" type="text" class="form-input w-full mt-1" value="{{ old('testimonial_3_name', $landingContent['testimonial_3_name'] ?? '') }}" required>
                        @error('testimonial_3_name') <p class="form-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label font-medium text-stone-700" for="testimonial_3_role">Author Role / Designation</label>
                        <input id="testimonial_3_role" name="testimonial_3_role" type="text" class="form-input w-full mt-1" value="{{ old('testimonial_3_role', $landingContent['testimonial_3_role'] ?? '') }}" required>
                        @error('testimonial_3_role') <p class="form-error mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label font-medium text-stone-700" for="testimonial_3_quote">Quote</label>
                        <textarea id="testimonial_3_quote" name="testimonial_3_quote" rows="4" class="form-input w-full mt-1" required>{{ old('testimonial_3_quote', $landingContent['testimonial_3_quote'] ?? '') }}</textarea>
                        @error('testimonial_3_quote') <p class="form-error mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-stone-200 flex justify-end">
                <button type="submit" class="btn-primary py-3 px-8">Save Testimonials</button>
            </div>
        </form>
    </section>
</div>
@endsection
