@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <section class="surface p-6 sm:p-8 lg:p-10">
        <span class="eyebrow">Feedbacks</span>
        <h1 class="mt-4 responsive-title lg:text-5xl">Review and manage guest feedback.</h1>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">
            Moderate guest reviews, check ratings, and approve testimonials to be featured on the public website.
        </p>
    </section>

    <!-- Stats Grid -->
    <section class="grid gap-4 sm:grid-cols-3">
        <!-- Card 1: Total Reviews -->
        <article class="card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.25em] text-stone-500">Total Reviews</p>
                    <p class="mt-4 text-4xl font-bold text-stone-950">{{ number_format($totalReviews) }}</p>
                    <p class="mt-3 text-xs text-stone-500">All submitted feedback</p>
                </div>
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-primary/10 to-brand-primary/5">
                    <svg class="h-8 w-8 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                    </svg>
                </div>
            </div>
        </article>

        <!-- Card 2: Pending Approval -->
        <article class="card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.25em] text-stone-500">Pending Approval</p>
                    <p class="mt-4 text-4xl font-bold text-stone-950">{{ number_format($pendingCount) }}</p>
                    <p class="mt-3 text-xs font-semibold {{ $pendingCount > 0 ? 'text-amber-600' : 'text-stone-500' }}">
                        {{ $pendingCount > 0 ? 'Needs attention' : 'All caught up' }}
                    </p>
                </div>
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br {{ $pendingCount > 0 ? 'from-amber-500/10 to-amber-500/5' : 'from-stone-200/50 to-stone-100/50' }}">
                    <svg class="h-8 w-8 {{ $pendingCount > 0 ? 'text-amber-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </article>

        <!-- Card 3: Average Rating -->
        <article class="card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.25em] text-stone-500">Average Rating</p>
                    <p class="mt-4 text-4xl font-bold text-stone-950">{{ number_format($averageRating, 1) }}<span class="text-2xl text-stone-400">/5</span></p>
                    <div class="mt-3 flex items-center gap-0.5 text-amber-500">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="h-5 w-5 {{ $i <= round($averageRating) ? 'fill-current' : 'text-stone-300 fill-none stroke-current' }}" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                </div>
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500/10 to-amber-500/5">
                    <svg class="h-8 w-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
            </div>
        </article>
    </section>

    <!-- Tabs Header -->
    <div class="flex flex-wrap gap-3" role="tablist" aria-label="Feedback moderation tabs">
        <button type="button" id="tab-pending-btn" class="btn-primary" onclick="switchTab('pending')">
            Pending Approval ({{ $pendingFeedbacks->count() }})
        </button>
        <button type="button" id="tab-approved-btn" class="btn-secondary" onclick="switchTab('approved')">
            Approved ({{ $approvedFeedbacks->count() }})
        </button>
    </div>

    <!-- Tab Contents -->
    <div class="space-y-6">
        <!-- Pending Feedbacks Tab -->
        <div id="tab-pending" class="grid gap-6 md:grid-cols-2">
            @forelse ($pendingFeedbacks as $review)
                <div class="surface p-6 flex flex-col justify-between hover:shadow-md transition duration-300">
                    <div class="space-y-4">
                        <!-- Top Meta -->
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-stone-900 text-lg">{{ $review->user?->name ?? 'Anonymous Guest' }}</h3>
                                <p class="text-xs text-stone-500 mt-0.5">Reviewed: <span class="font-medium text-stone-700">{{ $review->room?->name ?? 'Deleted Room' }}</span></p>
                            </div>
                            <span class="text-xs text-stone-400 font-medium uppercase tracking-wider">{{ $review->created_at->format('M d, Y') }}</span>
                        </div>

                        <!-- Rating Stars -->
                        <div class="flex items-center gap-0.5 text-amber-500">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-4 w-4 {{ $i <= $review->rating ? 'fill-current' : 'text-stone-300 fill-none stroke-current' }}" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>

                        <!-- Review Text -->
                        <p class="text-sm leading-relaxed text-stone-600 italic">
                            "{{ $review->comment }}"
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            Pending Approval
                        </span>
                        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-primary py-2 px-4 text-xs font-semibold">
                                Approve Review
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full surface p-10 text-center space-y-3">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-stone-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-stone-900">All reviews moderated</h3>
                    <p class="text-sm text-stone-500 max-w-sm mx-auto">There are no pending guest reviews waiting for approval at the moment.</p>
                </div>
            @endforelse
        </div>

        <!-- Approved Feedbacks Tab (Hidden by default) -->
        <div id="tab-approved" class="grid gap-6 md:grid-cols-2 hidden">
            @forelse ($approvedFeedbacks as $review)
                <div class="surface p-6 flex flex-col justify-between hover:shadow-md transition duration-300">
                    <div class="space-y-4">
                        <!-- Top Meta -->
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-stone-900 text-lg">{{ $review->user?->name ?? 'Anonymous Guest' }}</h3>
                                <p class="text-xs text-stone-500 mt-0.5">Reviewed: <span class="font-medium text-stone-700">{{ $review->room?->name ?? 'Deleted Room' }}</span></p>
                            </div>
                            <span class="text-xs text-stone-400 font-medium uppercase tracking-wider">{{ $review->created_at->format('M d, Y') }}</span>
                        </div>

                        <!-- Rating Stars -->
                        <div class="flex items-center gap-0.5 text-amber-500">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-4 w-4 {{ $i <= $review->rating ? 'fill-current' : 'text-stone-300 fill-none stroke-current' }}" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>

                        <!-- Review Text -->
                        <p class="text-sm leading-relaxed text-stone-600 italic">
                            "{{ $review->comment }}"
                        </p>
                    </div>

                    <!-- Status Indicator -->
                    <div class="mt-6 pt-4 border-t border-stone-100 flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            Approved & Public
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-full surface p-10 text-center space-y-3">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-stone-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-stone-900">No approved reviews</h3>
                    <p class="text-sm text-stone-500 max-w-sm mx-auto">None of the guest reviews have been approved to show on the public page yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        const pendingBtn = document.getElementById('tab-pending-btn');
        const approvedBtn = document.getElementById('tab-approved-btn');
        const pendingContent = document.getElementById('tab-pending');
        const approvedContent = document.getElementById('tab-approved');

        if (tab === 'pending') {
            // Update buttons
            pendingBtn.className = 'btn-primary';
            approvedBtn.className = 'btn-secondary';
            
            // Show/Hide content
            pendingContent.classList.remove('hidden');
            approvedContent.classList.add('hidden');
        } else if (tab === 'approved') {
            // Update buttons
            pendingBtn.className = 'btn-secondary';
            approvedBtn.className = 'btn-primary';
            
            // Show/Hide content
            pendingContent.classList.add('hidden');
            approvedContent.classList.remove('hidden');
        }
    }
</script>
@endsection
