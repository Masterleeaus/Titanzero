
<?php

use Illuminate\Support\Facades\Route;
use Modules\TitanZero\Http\Controllers\TitanZeroController;
use Modules\TitanZero\Http\Controllers\CleaningSuggestionsController;
use Modules\TitanZero\Http\Controllers\Account\AIChatProController;
use Modules\TitanZero\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettingsController;
use Modules\TitanZero\Http\Controllers\SuperAdmin\DashboardController;
use Modules\TitanZero\Http\Controllers\SuperAdmin\DiagnosticsController;
use Modules\TitanZero\Http\Controllers\SuperAdmin\LogsController;
use Modules\TitanZero\Http\Controllers\SuperAdmin\ChannelsController;
use Modules\TitanZero\Http\Controllers\SuperAdmin\PersonasController;
use Modules\TitanZero\Http\Controllers\SuperAdmin\PolicyController;
use Modules\TitanZero\Http\Controllers\SuperAdmin\ToolsController;
use Modules\TitanZero\Http\Controllers\SuperAdmin\WorkflowsController;
use Modules\TitanZero\Canvas\Http\Controllers\CanvasController;

// ── User-facing Titan Zero routes ──────────────────────────────────────────
Route::middleware(['web', 'auth'])
    ->prefix('account/titan/zero')
    ->name('titan.zero.')
    ->group(function () {
        Route::get('/', [TitanZeroController::class, 'index'])->name('index');
        Route::get('/help', [TitanZeroController::class, 'help'])->name('help');
        Route::get('/chat', [TitanZeroController::class, 'chat'])->name('chat');
        Route::get('/generators', [TitanZeroController::class, 'generators'])->name('generators');
        Route::get('/templates', [TitanZeroController::class, 'templates'])->name('templates');

        // ── Settings: features to enable ─────────────────────────────────
        Route::get('/settings', [TitanZeroController::class, 'settings'])->name('settings');
        Route::post('/settings', [TitanZeroController::class, 'saveSettings'])->name('settings.save');

        // ── AI Suggestion dashboard ───────────────────────────────────────
        Route::get('/suggestions', [CleaningSuggestionsController::class, 'dashboard'])->name('suggestions.dashboard');

        // ── Test AI suggestion for a given context ────────────────────────
        Route::post('/suggestions/test', [CleaningSuggestionsController::class, 'test'])->name('suggestions.test');

        // ── Cleaning business AI feature endpoints ────────────────────────
        Route::post('/suggestions/booking-slots', [CleaningSuggestionsController::class, 'bookingSlots'])->name('suggestions.booking-slots');
        Route::post('/suggestions/cleaner-match', [CleaningSuggestionsController::class, 'cleanerMatch'])->name('suggestions.cleaner-match');
        Route::post('/suggestions/auto-fill-instructions', [CleaningSuggestionsController::class, 'autoFillInstructions'])->name('suggestions.auto-fill-instructions');
        Route::post('/suggestions/price', [CleaningSuggestionsController::class, 'priceSuggestion'])->name('suggestions.price');
        Route::post('/suggestions/rebooking', [CleaningSuggestionsController::class, 'rebookingSuggestion'])->name('suggestions.rebooking');
        Route::post('/suggestions/sms-draft', [CleaningSuggestionsController::class, 'smsDraft'])->name('suggestions.sms-draft');
        Route::post('/suggestions/complaint-triage', [CleaningSuggestionsController::class, 'complaintTriage'])->name('suggestions.complaint-triage');
        Route::post('/suggestions/anomaly-detect', [CleaningSuggestionsController::class, 'anomalyDetect'])->name('suggestions.anomaly-detect');
        Route::post('/suggestions/automation-rules', [CleaningSuggestionsController::class, 'automationRules'])->name('suggestions.automation-rules');

        // ── AIChatPro ─────────────────────────────────────────────────────────
        Route::get('/ai-chat/{slug?}', [AIChatProController::class, 'index'])->name('ai-chat.index');
        Route::post('/ai-chat/message', [AIChatProController::class, 'sendMessage'])->name('ai-chat.send');
        Route::post('/ai-chat/session/new', [AIChatProController::class, 'startNewSession'])->name('ai-chat.session.new');

        // ── AiChatProFileChat v1.1.0 — document upload ────────────────────────
        Route::post('/ai-chat/file-upload', [AIChatProController::class, 'uploadFile'])->name('ai-chat.file-upload');

        // ── Canvas (AI Chat Pro TipTap Editor) ────────────────────────────────
        Route::post('/canvas/store', [CanvasController::class, 'storeContent'])->name('canvas.store');
        Route::post('/canvas/title', [CanvasController::class, 'saveTitle'])->name('canvas.title');

        // Legacy aliases kept for backwards compatibility
        Route::get('/business', fn () => redirect()->route('titan.zero.chat'))->name('business');
        Route::get('/foreman', fn () => redirect()->route('titan.zero.chat'))->name('foreman');
        Route::get('/compliance', fn () => redirect()->route('titan.zero.chat'))->name('compliance');

        // ── Coaches ───────────────────────────────────────────────────────
        Route::get('/coaches', [\Modules\TitanZero\Http\Controllers\Account\CoachController::class, 'index'])->name('coaches');
        Route::get('/coaches/{coachKey}', [\Modules\TitanZero\Http\Controllers\Account\CoachController::class, 'show'])->name('coaches.show');
        Route::post('/coaches/{coachKey}/ask', [\Modules\TitanZero\Http\Controllers\Account\CoachController::class, 'ask'])->name('coaches.ask');
        Route::get('/standards', fn () => redirect()->to('account/titan/zero/coaches/compliance'))->name('standards');

        // ── Intent Engine ─────────────────────────────────────────────────
        Route::post('/intent/resolve', [\Modules\TitanZero\Http\Controllers\IntentController::class, 'resolve'])->name('intent.resolve');
        Route::post('/intent/route', [\Modules\TitanZero\Http\Controllers\IntentController::class, 'route'])->name('intent.route');
        Route::post('/intent/confirm', [\Modules\TitanZero\Http\Controllers\IntentController::class, 'confirm'])->name('intent.confirm');

        // ── Wizards ───────────────────────────────────────────────────────
        Route::get('/wizards', [\Modules\TitanZero\Http\Controllers\WizardController::class, 'index'])->name('wizards');
        Route::post('/wizards/explain', [\Modules\TitanZero\Http\Controllers\WizardController::class, 'explainPage'])->name('wizards.explain');
        Route::post('/wizards/standards', [\Modules\TitanZero\Http\Controllers\WizardController::class, 'standardsQa'])->name('wizards.standards');
    });

// ── Super-admin Titan Zero routes ─────────────────────────────────────────
Route::middleware(['web', 'auth', 'super-admin'])
    ->prefix('dashboard/super-admin/titan-zero')
    ->name('superadmin.titan-zero.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/settings', [SuperAdminSettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SuperAdminSettingsController::class, 'store'])->name('settings.store');
        Route::get('/diagnostics', [DiagnosticsController::class, 'index'])->name('diagnostics.index');
        Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');
        Route::get('/channels', [ChannelsController::class, 'index'])->name('channels.index');
        Route::get('/personas', [PersonasController::class, 'index'])->name('personas.index');
        Route::get('/policy', [PolicyController::class, 'index'])->name('policy.index');
        Route::get('/tools', [ToolsController::class, 'index'])->name('tools.index');
        Route::get('/workflows', [WorkflowsController::class, 'index'])->name('workflows.index');
    });
