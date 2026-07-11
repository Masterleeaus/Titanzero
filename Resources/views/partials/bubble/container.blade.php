<div id="titanzero-bubble-root" data-tz-version="pass2">
    <button type="button" id="tz-bubble-btn" aria-label="Titan Zero" title="Titan Zero">
        <span class="tz-bubble-icon">⚡</span>
    </button>

    <div id="tz-panel" class="tz-panel" aria-hidden="true">
        <div class="tz-panel-header">
            <strong>Titan Zero</strong>
            <button type="button" id="tz-panel-close" aria-label="Close">✕</button>
        </div>
        <div id="tz-panel-body" class="tz-panel-body">
            <div class="tz-card">
                <div class="tz-card-title">Pass 2 installed</div>
                <div class="tz-card-body">Global bubble + page context capture are active. AI execution is wired in Pass 3.</div>
            </div>
            <div class="tz-card">
                <div class="tz-card-title">Current page context</div>
                <pre id="tz-context-preview" class="tz-pre"></pre>
            </div>
            <div class="tz-card">
                <div class="tz-card-title">Results</div>
                <div id="tz-results" class="tz-results"></div>
            </div>
        </div>
        <div class="tz-panel-footer">
            <button type="button" id="tz-refresh-context" class="tz-btn">Refresh context</button>
            <button type="button" id="tz-ping" class="tz-btn">Ping server</button>
            <button type="button" id="tz-action-explain" class="tz-btn">Explain</button>
            <button type="button" id="tz-action-missing" class="tz-btn">Check missing</button>
            <button type="button" id="tz-action-fill" class="tz-btn">Fill form</button>
            <button type="button" id="tz-action-notes" class="tz-btn">Notes</button>
        </div>
    </div>
</div>
