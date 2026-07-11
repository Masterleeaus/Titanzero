@php
    $user = auth()->user();
@endphp

@if($user && (property_exists($user, 'is_superadmin') ? $user->is_superadmin : ($user->is_admin ?? false)))
    <li class="sidebar-item {{ request()->routeIs('superadmin.titan-zero.*') ? 'active' : '' }}">
        <a class="sidebar-link" href="{{ route('superadmin.titan-zero.settings.index') }}">
            <i class="fa fa-magic"></i>
            <span>{{ __('Titan Zero') }}</span>
        </a>
    </li>
@endif

<li><a href="{{ url('dashboard/admin/settings/titan-zero/library/bulk') }}">Titan Zero • Bulk Tag Docs</a></li>
