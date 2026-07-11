
<?php

use Illuminate\Support\Facades\Route;
use Modules\TitanZero\Http\Controllers\Admin\AuditLogController;
use Modules\TitanZero\Http\Controllers\Admin\CoachAdminController;
use Modules\TitanZero\Http\Controllers\Admin\DoctorController;
use Modules\TitanZero\Http\Controllers\Admin\DocumentLibraryController;
use Modules\TitanZero\Http\Controllers\Admin\DocumentBulkController;
use Modules\TitanZero\Http\Controllers\Admin\ReviewQueueController;

Route::middleware(['web', 'auth', 'super-admin'])
    ->prefix('account')
    ->group(function () {

        // Titan Zero Document Library
        Route::get('/settings/titan-zero/library', [DocumentLibraryController::class, 'index'])
            ->name('titanzero.library.index');
        Route::get('/settings/titan-zero/library/upload', [DocumentLibraryController::class, 'upload'])
            ->name('titanzero.library.upload');
        Route::post('/settings/titan-zero/library', [DocumentLibraryController::class, 'store'])
            ->name('titanzero.library.store');
        Route::get('/settings/titan-zero/library/{id}', [DocumentLibraryController::class, 'show'])
            ->name('titanzero.library.show');
        Route::get('/settings/titan-zero/library/imports', [DocumentLibraryController::class, 'imports'])
            ->name('titanzero.library.imports');

        // Titan Zero Bulk Tag
        Route::get('/settings/titan-zero/library/bulk', [DocumentBulkController::class, 'index'])
            ->name('titanzero.library.bulk');
        Route::post('/settings/titan-zero/library/bulk', [DocumentBulkController::class, 'apply'])
            ->name('titanzero.library.bulk.apply');

        // Titan Zero Review Queue
        Route::get('/settings/titan-zero/review-queue', [ReviewQueueController::class, 'index'])
            ->name('titanzero.review-queue.index');
        Route::post('/settings/titan-zero/review-queue/{id}/approve', [ReviewQueueController::class, 'approve'])
            ->name('titanzero.review-queue.approve');

        // Titan Zero Audit Log
        Route::get('/settings/titan-zero/audit', [AuditLogController::class, 'index'])
            ->name('titanzero.audit.index');

        // Titan Zero Doctor (health check)
        Route::get('/settings/titan-zero/doctor', [DoctorController::class, 'index'])
            ->name('titanzero.doctor.index');

        // Titan Zero Coaches admin
        Route::get('/settings/titan-zero/coaches', [CoachAdminController::class, 'index'])
            ->name('titanzero.coaches.index');
        Route::get('/settings/titan-zero/coaches/{id}/edit', [CoachAdminController::class, 'edit'])
            ->name('titanzero.coaches.edit');
        Route::put('/settings/titan-zero/coaches/{id}', [CoachAdminController::class, 'update'])
            ->name('titanzero.coaches.update');
    });
