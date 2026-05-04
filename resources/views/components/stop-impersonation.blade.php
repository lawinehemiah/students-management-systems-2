@php
    $isImpersonating = session()->has('impersonate_admin_id') && session()->get('impersonating') === true;
    $adminRole = session('impersonate_admin_role');
@endphp

@if($isImpersonating)
<div style="position: fixed; bottom: 20px; right: 20px; z-index: 99999;">
    @if($adminRole == 'Head_of_Department')
        {{-- Use DIRECT URL instead of route name --}}
        <a href="{{ url('/hod/impersonate/stop') }}" 
           class="btn btn-danger btn-lg shadow-lg"
           style="border-radius: 50px; padding: 12px 24px; font-weight: bold; background: #dc3545; border: none;"
           onclick="event.preventDefault(); if(confirm('Stop impersonating and return to your HOD account?')) { window.location.href = this.href; }">
            <i class="fas fa-sign-out-alt me-2"></i> Stop Impersonating
        </a>
    @else
        <a href="{{ url('/superadmin/impersonate/stop') }}" 
           class="btn btn-danger btn-lg shadow-lg"
           style="border-radius: 50px; padding: 12px 24px; font-weight: bold; background: #dc3545; border: none;"
           onclick="event.preventDefault(); if(confirm('Stop impersonating and return to your account?')) { window.location.href = this.href; }">
            <i class="fas fa-sign-out-alt me-2"></i> Stop Impersonating
        </a>
    @endif
</div>
@endif