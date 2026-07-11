{{-- Titan Zero Pass 2: Template header & layout --}}
@include('titanzero::ai.partials.header')

{{-- Titan Zero Pass 1: architecture & UX baseline --}}
<a  class="btn btn-primary text-white btn-sm" data-size="lg" data-ajax-popup-over="true" data-url="{{ route('titan-zero.generate',[$template_module,$module]) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate Content With AI') }}">
    <i class="fas fa-robot"></i> {{ __('Generate with AI') }}
</a>


{{-- Titan Zero Pass 2: Standard options + result layout --}}
@include('titanzero::ai.partials.options')
@include('titanzero::ai.partials.result_panel')
