@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">{{ __('Titan Zero Templates') }}</h4>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                @forelse(($items ?? []) as $item)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold">{{ $item['name'] ?? '' }}</div>
                            @if(!empty($item['desc']))
                                <div class="text-muted small mt-1">{{ $item['desc'] }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-muted">{{ __('No items yet.') }}</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
