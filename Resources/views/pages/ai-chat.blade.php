@php
    $pageTitle = $pageTitle ?? __('AI Chat Pro');
    $pageIcon  = $pageIcon  ?? 'ti ti-message-circle';
@endphp

@extends('layouts.app')

@push('styles')
<style>
/* ── TitanZero AIChatPro ─────────────────────────────── */
.tz-chat-wrap          { display: flex; height: calc(100vh - 130px); min-height: 400px; gap: 0; }
.tz-chat-sidebar       { width: 260px; min-width: 180px; border-right: 1px solid var(--bs-border-color, #dee2e6);
                         display: flex; flex-direction: column; overflow: hidden; }
.tz-chat-sidebar-head  { padding: .75rem 1rem; font-weight: 600; font-size: .85rem;
                         border-bottom: 1px solid var(--bs-border-color, #dee2e6); }
.tz-cat-select         { padding: .5rem 1rem; border-bottom: 1px solid var(--bs-border-color, #dee2e6); }
.tz-session-list       { flex: 1; overflow-y: auto; padding: .25rem 0; }
.tz-session-item       { padding: .5rem 1rem; cursor: pointer; font-size: .83rem; white-space: nowrap;
                         overflow: hidden; text-overflow: ellipsis; border-radius: 0;
                         border-left: 3px solid transparent; transition: background .15s; }
.tz-session-item:hover { background: rgba(0,0,0,.04); }
.tz-session-item.active{ border-left-color: var(--bs-primary, #0d6efd);
                         background: rgba(13,110,253,.08); font-weight: 600; }
.tz-session-item .pin  { float: right; color: var(--bs-warning, #ffc107); font-size: .8rem; }

.tz-chat-main          { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.tz-chat-messages      { flex: 1; overflow-y: auto; padding: 1rem 1.25rem; }
.tz-msg                { margin-bottom: 1rem; display: flex; flex-direction: column; }
.tz-msg.user           { align-items: flex-end; }
.tz-msg.assistant      { align-items: flex-start; }
.tz-bubble             { max-width: 76%; padding: .6rem .9rem; border-radius: 1rem; font-size: .88rem;
                         line-height: 1.5; white-space: pre-wrap; word-break: break-word; }
.tz-msg.user .tz-bubble { background: var(--bs-primary, #0d6efd); color: #fff;
                          border-bottom-right-radius: .25rem; }
.tz-msg.assistant .tz-bubble { background: var(--bs-light, #f8f9fa);
                                border: 1px solid var(--bs-border-color, #dee2e6);
                                border-bottom-left-radius: .25rem; color: var(--bs-body-color, #212529); }
.tz-citations          { font-size: .78rem; color: var(--bs-secondary, #6c757d); margin-top: .25rem; }
.tz-typing-indicator   { display: none; align-items: center; gap: .3rem; padding: .5rem 1.25rem; color: #888; font-size: .83rem; }
.tz-typing-indicator.show { display: flex; }
.tz-dot                { width: 6px; height: 6px; border-radius: 50%; background: #888;
                         animation: tzDot .9s infinite ease-in-out; }
.tz-dot:nth-child(2) { animation-delay: .15s; }
.tz-dot:nth-child(3) { animation-delay: .30s; }
@keyframes tzDot { 0%,60%,100%{transform:scale(.8);opacity:.5} 30%{transform:scale(1.2);opacity:1} }

.tz-chat-input-bar     { padding: .75rem 1rem; border-top: 1px solid var(--bs-border-color, #dee2e6); display: flex; gap: .5rem; }
.tz-chat-input-bar textarea { flex: 1; resize: none; border-radius: .5rem; min-height: 42px; max-height: 140px; }
.tz-chat-input-bar button   { align-self: flex-end; }
.tz-empty-hint         { text-align: center; color: #aaa; margin-top: 3rem; font-size: .92rem; }

/* ── File Chat (AiChatProFileChat v1.1.0) ────────────────── */
.tz-file-badge         { display: flex; align-items: center; gap: .35rem; padding: .25rem .6rem;
                         background: rgba(13,110,253,.08); border: 1px solid rgba(13,110,253,.2);
                         border-radius: .4rem; font-size: .78rem; color: var(--bs-primary, #0d6efd);
                         max-width: 220px; overflow: hidden; }
.tz-file-badge span    { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }
.tz-file-input-wrap    { position: relative; }
.tz-file-input-wrap input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; }
.tz-upload-btn         { align-self: flex-end; position: relative; overflow: hidden; }

/* ── Canvas Panel ─────────────────────────────────────── */
.tz-canvas-panel       { display: none; flex-direction: column; width: 420px; min-width: 320px;
                         border-left: 1px solid var(--bs-border-color, #dee2e6); overflow: hidden; }
.tz-canvas-panel.open  { display: flex; }
.tz-canvas-header      { padding: .6rem 1rem; border-bottom: 1px solid var(--bs-border-color, #dee2e6);
                         display: flex; align-items: center; justify-content: space-between; gap: .5rem;
                         background: var(--bs-light, #f8f9fa); }
.tz-canvas-title-wrap  { display: flex; align-items: center; gap: .4rem; flex: 1; min-width: 0; }
.tz-canvas-icon        { flex-shrink: 0; color: var(--bs-primary, #0d6efd); }
.tz-canvas-title-input { border: none; background: transparent; flex: 1; font-weight: 600;
                         font-size: .9rem; min-width: 0; outline: none;
                         color: var(--bs-body-color, #212529); }
.tz-canvas-title-input::placeholder { color: #aaa; font-weight: 400; }
.tz-canvas-toolbar     { padding: .4rem .75rem; border-bottom: 1px solid var(--bs-border-color, #dee2e6);
                         background: var(--bs-white, #fff); display: flex; flex-wrap: wrap; gap: .2rem; }
.tz-canvas-editor-wrap { flex: 1; overflow-y: auto; }
.tz-canvas-editor      { min-height: 300px; padding: 1rem 1.25rem; outline: none;
                         font-size: .88rem; line-height: 1.6; }
.tz-canvas-editor p    { margin-bottom: .6rem; }
.tz-canvas-editor h1,.tz-canvas-editor h2,.tz-canvas-editor h3 { margin: .8rem 0 .4rem; font-weight: 600; }
.tz-canvas-editor ul,.tz-canvas-editor ol { padding-left: 1.5rem; margin-bottom: .6rem; }
.tz-canvas-editor blockquote { border-left: 3px solid var(--bs-primary, #0d6efd);
                                padding: .25rem .75rem; color: var(--bs-secondary, #6c757d); margin: .5rem 0; }
.tz-canvas-editor pre  { background: var(--bs-light, #f8f9fa); padding: .75rem 1rem;
                         border-radius: .375rem; overflow-x: auto; font-size: .82rem; margin-bottom: .6rem; }
.tz-tb-btn.is-active   { background: var(--bs-primary, #0d6efd) !important; color: #fff !important; }
/* ── "Open in Canvas" button on messages ────────────────── */
.tz-canvas-open-btn    { display: inline-flex; align-items: center; gap: .25rem; margin-top: .35rem;
                         font-size: .75rem; opacity: .7; padding: 2px 6px; border-radius: .25rem; }
</style>
@endpush

@section('content')
<div class="container-fluid py-2 px-3">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
            <h5 class="mb-0"><i class="ti ti-message-circle me-1"></i>{{ __('AI Chat Pro') }}</h5>
            <small class="text-muted">
                {{ $category->name ?? __('AI Chat') }}
                @if(!empty($category->helps_with))
                    — {{ $category->helps_with }}
                @endif
            </small>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="tz-chat-wrap">

            {{-- Sidebar --}}
            <div class="tz-chat-sidebar">
                <div class="tz-chat-sidebar-head">
                    <i class="ti ti-list me-1"></i>{{ __('Conversations') }}
                </div>

                {{-- Category picker --}}
                @if($categories->count() > 1)
                <div class="tz-cat-select">
                    <select id="tzCategorySelect" class="form-select form-select-sm">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}"
                                {{ isset($category) && $category->id === $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Session list --}}
                <div class="tz-session-list" id="tzSessionList">
                    @forelse($list as $sess)
                        <div class="tz-session-item {{ isset($chat) && $chat->id === $sess->id ? 'active' : '' }}"
                             data-session-id="{{ $sess->id }}"
                             title="{{ $sess->title }}">
                            @if($sess->is_pinned)<span class="pin">★</span>@endif
                            {{ Str::limit($sess->title, 30) }}
                        </div>
                    @empty
                        <div class="text-muted small p-3">{{ __('No conversations yet.') }}</div>
                    @endforelse
                </div>

                {{-- New chat button --}}
                @auth
                <div class="p-2 border-top">
                    <button class="btn btn-outline-primary btn-sm w-100" id="tzNewSessionBtn"
                            data-category-id="{{ $category->id ?? '' }}">
                        <i class="ti ti-plus me-1"></i>{{ __('New Chat') }}
                    </button>
                </div>
                @endauth
            </div>

            {{-- Main chat area --}}
            <div class="tz-chat-main">
                <div class="tz-chat-messages" id="tzMessages">
                    @if(!isset($chat))
                        <div class="tz-empty-hint">
                            <i class="ti ti-message-circle" style="font-size:2rem;"></i><br>
                            {{ __('Select a conversation or start a new one.') }}
                        </div>
                    @else
                        @foreach($chat->messages()->orderBy('created_at', 'asc')->get() as $msg)
                            @if($msg->output && !$msg->input)
                                {{-- Greeting / system init --}}
                                <div class="tz-msg assistant">
                                    <div class="tz-bubble">{{ $msg->output }}</div>
                                </div>
                            @endif
                            @if($msg->input)
                                <div class="tz-msg user">
                                    <div class="tz-bubble">{{ $msg->input }}</div>
                                </div>
                                @if($msg->output)
                                    <div class="tz-msg assistant" data-message-id="{{ $msg->id }}">
                                        <div class="tz-bubble">{!! nl2br(e($msg->output)) !!}</div>
                                        <button
                                            class="btn btn-outline-secondary btn-sm tz-canvas-open-btn"
                                            data-canvas-open="{{ $msg->id }}"
                                            data-canvas-text="{{ e($msg->output) }}"
                                            title="{{ __('Open in Canvas') }}"
                                        >
                                            <svg width="14" height="12" viewBox="0 0 22 20" fill="none" stroke="currentColor" stroke-width="1.85">
                                                <path d="M4.875 14.125H11.875M15.75 15V5M4.875 10.0625H11.875M4.875 6.0625H11.875M2.875 19C2.325 19 1.85417 18.8042 1.4625 18.4125C1.07083 18.0208 0.875 17.55 0.875 17V3C0.875 2.45 1.07083 1.97917 1.4625 1.5875C1.85417 1.19583 2.325 1 2.875 1H18.875C19.425 1 19.8958 1.19583 20.2875 1.5875C20.6792 1.97917 20.875 2.45 20.875 3V17C20.875 17.55 20.6792 18.0208 20.2875 18.4125C19.8958 18.8042 19.425 19 18.875 19H2.875Z"/>
                                            </svg>
                                            {{ __('Open in Canvas') }}
                                        </button>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    @endif
                </div>

                <div class="tz-typing-indicator" id="tzTyping">
                    <span class="tz-dot"></span><span class="tz-dot"></span><span class="tz-dot"></span>
                    <span>{{ __('Titan is thinking…') }}</span>
                </div>

                {{-- Document badge: shown when a file is attached to the session --}}
                @if(isset($chat) && $chat->doc_name)
                <div class="tz-file-badge ms-3 mb-1" id="tzFileBadge">
                    <i class="ti ti-file-text" style="flex-shrink:0;"></i>
                    <span title="{{ $chat->doc_name }}">{{ $chat->doc_name }}</span>
                </div>
                @else
                <div class="tz-file-badge ms-3 mb-1 d-none" id="tzFileBadge">
                    <i class="ti ti-file-text" style="flex-shrink:0;"></i>
                    <span id="tzFileDocName"></span>
                </div>
                @endif

                <div class="tz-chat-input-bar">
                    @if(config('titanzero.file_chat.allowed', true) && isset($chat))
                    <div class="tz-upload-btn" title="{{ __('Upload a document (PDF, DOCX, TXT)') }}">
                        <button class="btn btn-outline-secondary" id="tzUploadBtn"
                                {{ !isset($chat) ? 'disabled' : '' }}
                                type="button">
                            <i class="ti ti-paperclip" id="tzUploadIcon"></i>
                        </button>
                        <input type="file"
                               id="tzFileInput"
                               accept=".pdf,.docx,.doc,.txt,.csv,.xlsx"
                               {{ !isset($chat) ? 'disabled' : '' }}>
                    </div>
                    @endif
                    <textarea id="tzInput" class="form-control"
                              placeholder="{{ __('Type a message…') }}"
                              rows="1"
                              {{ !isset($chat) ? 'disabled' : '' }}></textarea>
                    <button class="btn btn-primary" id="tzSendBtn"
                            {{ !isset($chat) ? 'disabled' : '' }}>
                        <i class="ti ti-send"></i>
                    </button>
                </div>
            </div>

            {{-- Canvas Panel (TipTap Editor) --}}
            @include('titanzero::canvas.panel')

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@tiptap/core@2.11.5/dist/tiptap-core.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/pm@2.11.5/dist/tiptap-pm.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/starter-kit@2.11.5/dist/tiptap-starter-kit.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-underline@2.11.5/dist/tiptap-extension-underline.umd.min.js"></script>
<script>
(function () {
    'use strict';

    const SEND_URL       = @json(route('titan.zero.ai-chat.send'));
    const NEW_SESS_URL   = @json(route('titan.zero.ai-chat.session.new'));
    const CHAT_BASE_URL  = @json(rtrim(route('titan.zero.ai-chat.index'), '/'));
    const CANVAS_STORE   = @json(route('titan.zero.canvas.store'));
    const CANVAS_TITLE   = @json(route('titan.zero.canvas.title'));
    const FILE_UPLOAD_URL= @json(route('titan.zero.ai-chat.file-upload'));
    const CSRF           = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    let currentSessionId = {{ $chat?->id ?? 'null' }};

    const msgBox   = document.getElementById('tzMessages');
    const input    = document.getElementById('tzInput');
    const sendBtn  = document.getElementById('tzSendBtn');
    const typing   = document.getElementById('tzTyping');
    const sessList = document.getElementById('tzSessionList');
    const newBtn   = document.getElementById('tzNewSessionBtn');
    const catSel   = document.getElementById('tzCategorySelect');

    /* ── Scroll to bottom ─────────────────────────────────── */
    function scrollDown() {
        if (msgBox) msgBox.scrollTop = msgBox.scrollHeight;
    }
    scrollDown();

    /* ── Auto-resize textarea ─────────────────────────────── */
    if (input) {
        input.addEventListener('input', () => {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 140) + 'px';
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); doSend(); }
        });
    }
    if (sendBtn) sendBtn.addEventListener('click', doSend);

    /* ── Send message ─────────────────────────────────────── */
    async function doSend() {
        const msg = (input?.value ?? '').trim();
        if (!msg || !currentSessionId) return;

        appendBubble('user', msg, null, null);
        input.value = '';
        input.style.height = 'auto';
        setInputEnabled(false);
        typing.classList.add('show');
        scrollDown();

        try {
            const res = await fetch(SEND_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ message: msg, session_id: currentSessionId }),
            });
            const data = await res.json();
            typing.classList.remove('show');
            if (data.ok) {
                appendBubble('assistant', data.reply ?? '', data.citations ?? [], data.message_id ?? null);
            } else {
                appendBubble('assistant', data.message ?? '{{ __("An error occurred.") }}', [], null);
            }
        } catch (err) {
            typing.classList.remove('show');
            appendBubble('assistant', '{{ __("Connection error. Please try again.") }}', [], null);
        }
        setInputEnabled(true);
        scrollDown();
    }

    function appendBubble(role, text, citations, messageId) {
        const wrap = document.createElement('div');
        wrap.className = 'tz-msg ' + role;
        if (messageId) wrap.dataset.messageId = messageId;

        const bubble = document.createElement('div');
        bubble.className = 'tz-bubble';
        bubble.textContent = text;
        wrap.appendChild(bubble);

        if (citations && citations.length) {
            const citeEl = document.createElement('div');
            citeEl.className = 'tz-citations';
            citeEl.textContent = '{{ __("Sources:") }} ' +
                citations.map(c => c.document_title || c.document_id).filter(Boolean).join(', ');
            wrap.appendChild(citeEl);
        }

        if (role === 'assistant' && messageId) {
            const canvasBtn = document.createElement('button');
            canvasBtn.className = 'btn btn-outline-secondary btn-sm tz-canvas-open-btn';
            canvasBtn.dataset.canvasOpen = messageId;
            canvasBtn.dataset.canvasText = text;
            canvasBtn.title = '{{ __("Open in Canvas") }}';
            canvasBtn.innerHTML =
                '<svg width="14" height="12" viewBox="0 0 22 20" fill="none" stroke="currentColor" stroke-width="1.85">' +
                '<path d="M4.875 14.125H11.875M15.75 15V5M4.875 10.0625H11.875M4.875 6.0625H11.875M2.875 19C2.325 19 1.85417 18.8042 1.4625 18.4125C1.07083 18.0208 0.875 17.55 0.875 17V3C0.875 2.45 1.07083 1.97917 1.4625 1.5875C1.85417 1.19583 2.325 1 2.875 1H18.875C19.425 1 19.8958 1.19583 20.2875 1.5875C20.6792 1.97917 20.875 2.45 20.875 3V17C20.875 17.55 20.6792 18.0208 20.2875 18.4125C19.8958 18.8042 19.425 19 18.875 19H2.875Z"/>' +
                '</svg> {{ __("Open in Canvas") }}';
            wrap.appendChild(canvasBtn);
        }

        msgBox.appendChild(wrap);
    }

    function setInputEnabled(on) {
        if (input)   input.disabled   = !on;
        if (sendBtn) sendBtn.disabled = !on;
    }

    /* ── Session list click ───────────────────────────────── */
    if (sessList) {
        sessList.addEventListener('click', (e) => {
            const item = e.target.closest('.tz-session-item');
            if (!item) return;
            const sid = item.dataset.sessionId;
            window.location.href = CHAT_BASE_URL + '/{{ $category?->slug ?? "" }}?session=' + sid;
        });
    }

    /* ── New session ──────────────────────────────────────── */
    if (newBtn) {
        newBtn.addEventListener('click', async () => {
            const catId = newBtn.dataset.categoryId;
            if (!catId) return;
            newBtn.disabled = true;
            try {
                const res = await fetch(NEW_SESS_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ category_id: catId }),
                });
                const data = await res.json();
                if (data.ok) {
                    window.location.href = CHAT_BASE_URL + '/{{ $category?->slug ?? "" }}';
                }
            } finally {
                newBtn.disabled = false;
            }
        });
    }

    /* ── Category switch ──────────────────────────────────── */
    if (catSel) {
        catSel.addEventListener('change', () => {
            window.location.href = CHAT_BASE_URL + '/' + catSel.value;
        });
    }

    /* ═══════════════════════════════════════════════════════
       Canvas — TipTap Editor
    ═══════════════════════════════════════════════════════ */
    let tiptapEditor   = null;
    let canvasMessageId = null;

    const canvasPanel  = document.getElementById('tzCanvasPanel');
    const canvasTitle  = document.getElementById('tzCanvasTitle');
    const canvasSave   = document.getElementById('tzCanvasSaveBtn');
    const canvasClose  = document.getElementById('tzCanvasCloseBtn');
    const editorEl     = document.getElementById('tzTiptapEditor');

    function initTiptap() {
        if (!window.StarterKit || !editorEl) return;

        if (tiptapEditor) {
            tiptapEditor.destroy();
            tiptapEditor = null;
        }

        const extensions = [window.StarterKit];
        const underlineExt = window.TiptapUnderline || window.Underline || window.TiptapExtensionUnderline;
        if (underlineExt) extensions.push(underlineExt);

        tiptapEditor = new window.tiptapCore.Editor({
            element: editorEl,
            extensions,
            content: '',
            editorProps: {
                attributes: { class: 'tz-canvas-editor', style: 'outline:none;' },
            },
        });

        // Sync toolbar active-state on selection change
        tiptapEditor.on('selectionUpdate', syncToolbarState);
        tiptapEditor.on('transaction',     syncToolbarState);
    }

    function syncToolbarState() {
        if (!tiptapEditor) return;
        document.querySelectorAll('.tz-tb-btn').forEach(btn => {
            const action = btn.dataset.action;
            let active = false;
            if (action === 'bold')      active = tiptapEditor.isActive('bold');
            if (action === 'italic')    active = tiptapEditor.isActive('italic');
            if (action === 'underline') active = tiptapEditor.isActive('underline');
            if (action === 'h1')        active = tiptapEditor.isActive('heading', { level: 1 });
            if (action === 'h2')        active = tiptapEditor.isActive('heading', { level: 2 });
            if (action === 'h3')        active = tiptapEditor.isActive('heading', { level: 3 });
            if (action === 'bullet')    active = tiptapEditor.isActive('bulletList');
            if (action === 'ordered')   active = tiptapEditor.isActive('orderedList');
            if (action === 'blockquote')active = tiptapEditor.isActive('blockquote');
            if (action === 'code')      active = tiptapEditor.isActive('codeBlock');
            btn.classList.toggle('is-active', active);
        });
    }

    /* Toolbar actions */
    document.querySelectorAll('.tz-tb-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!tiptapEditor) return;
            const action = btn.dataset.action;
            const chain = tiptapEditor.chain().focus();
            if (action === 'bold')       chain.toggleBold().run();
            if (action === 'italic')     chain.toggleItalic().run();
            if (action === 'underline')  chain.toggleUnderline().run();
            if (action === 'h1')         chain.toggleHeading({ level: 1 }).run();
            if (action === 'h2')         chain.toggleHeading({ level: 2 }).run();
            if (action === 'h3')         chain.toggleHeading({ level: 3 }).run();
            if (action === 'bullet')     chain.toggleBulletList().run();
            if (action === 'ordered')    chain.toggleOrderedList().run();
            if (action === 'blockquote') chain.toggleBlockquote().run();
            if (action === 'code')       chain.toggleCodeBlock().run();
        });
    });

    /* Open canvas with message content */
    function openCanvas(messageId, text) {
        canvasMessageId = messageId;

        if (!tiptapEditor) {
            initTiptap();
        }

        // Set editor content — use plain text to avoid XSS
        if (tiptapEditor) {
            tiptapEditor.commands.clearContent();
            tiptapEditor.commands.setContent({
                type: 'doc',
                content: (text || '').split('\n').filter(Boolean).map(line => ({
                    type: 'paragraph',
                    content: [{ type: 'text', text: line }],
                })),
            });
        }

        canvasTitle.value = '';
        canvasPanel.classList.add('open');
    }

    /* Close canvas */
    if (canvasClose) {
        canvasClose.addEventListener('click', () => {
            canvasPanel.classList.remove('open');
        });
    }

    /* Auto-save title on blur/enter */
    if (canvasTitle) {
        const saveTitle = async () => {
            if (!canvasMessageId) return;
            await fetch(CANVAS_TITLE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ message_id: canvasMessageId, title: canvasTitle.value }),
            });
        };
        canvasTitle.addEventListener('blur',    saveTitle);
        canvasTitle.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); saveTitle(); } });
    }

    /* Save canvas content */
    if (canvasSave) {
        canvasSave.addEventListener('click', async () => {
            if (!canvasMessageId || !tiptapEditor) return;

            canvasSave.disabled = true;
            canvasSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>{{ __("Saving…") }}';

            try {
                const output = JSON.stringify(tiptapEditor.getJSON());

                const [storeRes, titleRes] = await Promise.all([
                    fetch(CANVAS_STORE, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ message_id: canvasMessageId, type: 'output', content: output }),
                    }),
                    fetch(CANVAS_TITLE, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ message_id: canvasMessageId, title: canvasTitle.value }),
                    }),
                ]);

                const storeData = await storeRes.json();

                if (storeData.status === 'success') {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('{{ __("Saved successfully") }}');
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(storeData.message || '{{ __("Save failed") }}');
                    }
                }
            } catch (err) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ __("Connection error. Please try again.") }}');
                }
            } finally {
                canvasSave.disabled = false;
                canvasSave.innerHTML = '<i class="ti ti-device-floppy me-1"></i>{{ __("Save") }}';
            }
        });
    }

    /* Delegate "Open in Canvas" button clicks (covers both server-rendered and dynamic bubbles) */
    if (msgBox) {
        msgBox.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-canvas-open]');
            if (!btn) return;
            e.preventDefault();
            openCanvas(btn.dataset.canvasOpen, btn.dataset.canvasText);
        });
    }

    /* ═══════════════════════════════════════════════════════
       File upload — AiChatProFileChat v1.1.0
    ═══════════════════════════════════════════════════════ */
    const fileInput  = document.getElementById('tzFileInput');
    const uploadIcon = document.getElementById('tzUploadIcon');
    const fileBadge  = document.getElementById('tzFileBadge');
    const fileDocName= document.getElementById('tzFileDocName');

    if (fileInput) {
        fileInput.addEventListener('change', async () => {
            const file = fileInput.files?.[0];
            if (!file || !currentSessionId) return;

            if (uploadIcon) {
                uploadIcon.className = 'spinner-border spinner-border-sm';
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('session_id', currentSessionId);
            formData.append('_token', CSRF);

            try {
                const res  = await fetch(FILE_UPLOAD_URL, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData,
                });
                const data = await res.json();

                if (data.ok) {
                    if (fileBadge)  { fileBadge.classList.remove('d-none'); }
                    if (fileDocName){ fileDocName.textContent = data.doc_name || file.name; }
                    if (typeof toastr !== 'undefined') {
                        toastr.success('{{ __("Document uploaded and indexed successfully.") }}');
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || '{{ __("File upload failed.") }}');
                    }
                }
            } catch (err) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('{{ __("Connection error during file upload.") }}');
                }
            } finally {
                if (uploadIcon) {
                    uploadIcon.className = 'ti ti-paperclip';
                }
                // Reset input so the same file can be re-uploaded if needed
                fileInput.value = '';
            }
        });
    }

})();
</script>
@endpush
