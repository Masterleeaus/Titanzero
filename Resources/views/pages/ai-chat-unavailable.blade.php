@extends('layouts.app')

@section('content')
<div class="container py-5 text-center">
    <div class="mb-3"><i class="ti ti-message-circle" style="font-size:3rem;color:#aaa;"></i></div>
    <h5 class="mb-2">{{ __('AI Chat Unavailable') }}</h5>
    <p class="text-muted">{{ __('No AI chat categories have been configured yet. Please contact an administrator.') }}</p>
</div>
@endsection
