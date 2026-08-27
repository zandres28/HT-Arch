<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliverableController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WorkLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Proyectos
Route::resource('projects', ProjectController::class);
Route::post('projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
Route::post('projects/{project}/unarchive', [ProjectController::class, 'unarchive'])->name('projects.unarchive');

// Registros de horas
Route::resource('work-logs', WorkLogController::class);
Route::post('work-logs/{work_log}/duplicate', [WorkLogController::class, 'duplicate'])->name('work-logs.duplicate');

// Evidencias
Route::post('work-logs/{work_log}/attachments', [AttachmentController::class, 'store'])->name('attachments.store');
Route::get('attachments/{attachment}/view', [AttachmentController::class, 'view'])->name('attachments.view');
Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

// Entregables
Route::resource('deliverables', DeliverableController::class)->except(['index', 'show']);
Route::get('deliverables/{deliverable}/download', [DeliverableController::class, 'download'])->name('deliverables.download');

// Calendario
Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');

// Reportes
Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
Route::get('reports/csv', [ReportController::class, 'csv'])->name('reports.csv');

// Respaldo
Route::get('backup', [BackupController::class, 'index'])->name('backup.index');
Route::post('backup/export', [BackupController::class, 'export'])->name('backup.export');
Route::post('backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
