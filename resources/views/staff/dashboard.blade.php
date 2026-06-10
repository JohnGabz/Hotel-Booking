@extends('layouts.dashboard')

@section('sidebar-nav')
    <a href="{{ route('staff.dashboard') }}" data-dashboard-section="bookings" class="nav-link flex items-center gap-3 {{ ($activeSection ?? 'bookings') === 'bookings' ? 'nav-link-active' : '' }}">Bookings</a>
    <a href="{{ route('staff.dashboard', ['section' => 'guests']) }}" data-dashboard-section="guests" class="nav-link flex items-center gap-3 {{ ($activeSection ?? '') === 'guests' ? 'nav-link-active' : '' }}">Guests</a>
@endsection

@section('content')
    <div id="dashboard-section-content">
        @include('staff.sections.' . ($activeSection ?? 'bookings'))
    </div>

    @include('partials.dashboard-ajax-loader')
@endsection
