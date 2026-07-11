{{-- Titan Zero Pass 3: Super Admin usage & quota overview --}}
@extends('titanzero::layouts.master')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3 mb-1">{{ __('Titan Zero usage') }}</h1>
                <p class="text-muted mb-0">
                    {{ __('Review recent Titan Zero usage and adjust soft limits if required.') }}
                </p>
            </div>
        </div>

        @isset($quota)
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <strong>{{ __('Soft limits') }}</strong>
                        </div>
                        <div class="card-body">
                            <p class="mb-1">
                                {{ __('Daily requests per user') }}:
                                <strong>{{ $quota['daily_requests_per_user'] ?? '—' }}</strong>
                            </p>
                            <p class="mb-0">
                                {{ __('Daily tokens per user') }}:
                                <strong>{{ $quota['daily_tokens_per_user'] ?? '—' }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endisset

        @isset($usages)
            <div class="row">
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            <strong>{{ __('Recent usage (today)') }}</strong>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('User ID') }}</th>
                                        <th>{{ __('Company ID') }}</th>
                                        <th>{{ __('Template ID') }}</th>
                                        <th>{{ __('Tokens used') }}</th>
                                        <th>{{ __('Requests') }}</th>
                                        <th>{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($usages as $usage)
                                        <tr>
                                            <td>{{ $usage->user_id }}</td>
                                            <td>{{ $usage->company_id }}</td>
                                            <td>{{ $usage->template_id }}</td>
                                            <td>{{ $usage->tokens_used }}</td>
                                            <td>{{ $usage->requests_count }}</td>
                                            <td>{{ $usage->created_at }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                {{ __('No Titan Zero usage logged for today yet.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endisset
    </div>
@endsection
