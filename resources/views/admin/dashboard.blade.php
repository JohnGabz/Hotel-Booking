@extends('layouts.dashboard')

@section('sidebar-nav')
    <a href="{{ route('admin.dashboard') }}" data-dashboard-section="dashboard" class="nav-link flex items-center gap-3 {{ ($activeSection ?? 'dashboard') === 'dashboard' ? 'nav-link-active' : '' }}">Dashboard</a>
    <a href="{{ route('admin.dashboard', ['section' => 'bookings']) }}" data-dashboard-section="bookings" class="nav-link flex items-center gap-3 {{ ($activeSection ?? '') === 'bookings' ? 'nav-link-active' : '' }}">Bookings</a>
    <a href="{{ route('admin.dashboard', ['section' => 'rooms']) }}" data-dashboard-section="rooms" class="nav-link flex items-center gap-3 {{ ($activeSection ?? '') === 'rooms' ? 'nav-link-active' : '' }}">Rooms</a>
    <a href="{{ route('admin.dashboard', ['section' => 'users']) }}" data-dashboard-section="users" class="nav-link flex items-center gap-3 {{ ($activeSection ?? '') === 'users' ? 'nav-link-active' : '' }}">Users</a>
    <a href="{{ route('admin.guests') }}" class="nav-link flex items-center gap-3">Guests (full page)</a>
    <a href="{{ route('admin.reports') }}" class="nav-link flex items-center gap-3">Reports</a>
    <a href="{{ route('admin.settings') }}" class="nav-link flex items-center gap-3">Settings</a>
@endsection

@section('content')
    <div id="dashboard-section-content">
        @include('admin.sections.' . ($activeSection ?? 'dashboard'))
    </div>

    @include('partials.dashboard-ajax-loader')
@endsection
