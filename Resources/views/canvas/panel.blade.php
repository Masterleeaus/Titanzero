{{--
    TitanZero Canvas — TipTap Editor Panel
    Rendered alongside the AIChatPro chat view when user clicks "Open in Canvas".
    Depends on TipTap loaded from CDN (added via @push('scripts') below).
--}}
<div class="tz-canvas-panel" id="tzCanvasPanel">
    <div class="tz-canvas-header">
        <div class="tz-canvas-title-wrap">
            <svg class="tz-canvas-icon" width="18" height="16" viewBox="0 0 22 20" fill="none" stroke="currentColor" stroke-width="1.85">
                <path d="M4.875 14.125H11.875M15.75 15V5M4.875 10.0625H11.875M4.875 6.0625H11.875M2.875 19C2.325 19 1.85417 18.8042 1.4625 18.4125C1.07083 18.0208 0.875 17.55 0.875 17V3C0.875 2.45 1.07083 1.97917 1.4625 1.5875C1.85417 1.19583 2.325 1 2.875 1H18.875C19.425 1 19.8958 1.19583 20.2875 1.5875C20.6792 1.97917 20.875 2.45 20.875 3V17C20.875 17.55 20.6792 18.0208 20.2875 18.4125C19.8958 18.8042 19.425 19 18.875 19H2.875Z"/>
            </svg>
            <input
                type="text"
                id="tzCanvasTitle"
                class="tz-canvas-title-input"
                placeholder="{{ __('Untitled Document') }}"
                maxlength="255"
            >
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-sm btn-primary" id="tzCanvasSaveBtn">
                <i class="ti ti-device-floppy me-1"></i>{{ __('Save') }}
            </button>
            <button class="btn btn-sm btn-outline-secondary" id="tzCanvasCloseBtn" title="{{ __('Close Canvas') }}">
                <i class="ti ti-x"></i>
            </button>
        </div>
    </div>

    <div class="tz-canvas-toolbar" id="tzCanvasToolbar">
        <div class="btn-group btn-group-sm me-1" role="group">
            <button type="button" class="btn btn-outline-secondary tz-tb-btn" data-action="bold" title="{{ __('Bold') }}"><strong>B</strong></button>
            <button type="button" class="btn btn-outline-secondary tz-tb-btn" data-action="italic" title="{{ __('Italic') }}"><em>I</em></button>
            <button type="button" class="btn btn-outline-secondary tz-tb-btn" data-action="underline" title="{{ __('Underline') }}"><u>U</u></button>
        </div>
        <div class="btn-group btn-group-sm me-1" role="group">
            <button type="button" class="btn btn-outline-secondary tz-tb-btn" data-action="h1" title="{{ __('Heading 1') }}">H1</button>
            <button type="button" class="btn btn-outline-secondary tz-tb-btn" data-action="h2" title="{{ __('Heading 2') }}">H2</button>
            <button type="button" class="btn btn-outline-secondary tz-tb-btn" data-action="h3" title="{{ __('Heading 3') }}">H3</button>
        </div>
        <div class="btn-group btn-group-sm me-1" role="group">
            <button type="button" class="btn btn-outline-secondary tz-tb-btn" data-action="bullet" title="{{ __('Bullet List') }}">
                <i class="ti ti-list"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary tz-tb-btn" data-action="ordered" title="{{ __('Ordered List') }}">
                <i class="ti ti-list-numbers"></i>
            </button>
        </div>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary tz-tb-btn" data-action="blockquote" title="{{ __('Blockquote') }}">
                <i class="ti ti-quote"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary tz-tb-btn" data-action="code" title="{{ __('Code Block') }}">
                <i class="ti ti-code"></i>
            </button>
        </div>
    </div>

    <div class="tz-canvas-editor-wrap">
        <div id="tzTiptapEditor" class="tz-canvas-editor"></div>
    </div>
</div>
