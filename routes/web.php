<?php

use App\Http\Controllers\PortalAssistantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectorySyncController;
use App\Http\Controllers\DocumentActivityLogController;
use App\Http\Controllers\FormTemplateController;
use App\Http\Controllers\LookupValueController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PolicyDocumentController;
use App\Http\Controllers\ReportingDashboardController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\TopicCategoryController;
use App\Http\Controllers\UserAccessReportController;
use App\Http\Controllers\ViewerSessionController;
use App\Http\Middleware\EnsureFormBuilderEnabled;
use App\Models\PolicyDocument;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/csrf-token', fn () => response()
    ->json(['token' => csrf_token()])
    ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0'))
    ->name('csrf-token');

Route::post('/viewer-session', [ViewerSessionController::class, 'store'])->name('viewer-session.store');
Route::delete('/viewer-session', [ViewerSessionController::class, 'destroy'])->name('viewer-session.destroy');
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::patch('/notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

Route::get('/policy-documents', [PolicyDocumentController::class, 'index'])->name('policy-documents.index');
Route::get('/policy-documents/create', [PolicyDocumentController::class, 'create'])->name('policy-documents.create');
Route::post('/policy-documents', [PolicyDocumentController::class, 'store'])->name('policy-documents.store');
Route::get('/policy-documents/{policyDocument}', [PolicyDocumentController::class, 'show'])->name('policy-documents.show');
Route::get('/policy-documents/{policyDocument}/download', [PolicyDocumentController::class, 'download'])->name('policy-documents.download');
Route::get('/policy-documents/{policyDocument}/preview', [PolicyDocumentController::class, 'preview'])->name('policy-documents.preview');
Route::get('/document-attachments/{documentAttachment}/download', [PolicyDocumentController::class, 'downloadAttachment'])->name('document-attachments.download');
Route::get('/document-attachments/{documentAttachment}/preview', [PolicyDocumentController::class, 'previewAttachment'])->name('document-attachments.preview');
Route::delete('/document-attachments/{documentAttachment}', [PolicyDocumentController::class, 'destroyAttachment'])->name('document-attachments.destroy');
Route::get('/policy-documents/{policyDocument}/edit', [PolicyDocumentController::class, 'edit'])->name('policy-documents.edit');
Route::put('/policy-documents/{policyDocument}', [PolicyDocumentController::class, 'update'])->name('policy-documents.update');
Route::delete('/policy-documents/{policyDocument}', [PolicyDocumentController::class, 'destroy'])->name('policy-documents.destroy');
Route::post('/policy-documents/{policyDocument}/publish', [PolicyDocumentController::class, 'publish'])->name('policy-documents.publish');
Route::get('/policy-documents/{policyDocument}/versions', fn (PolicyDocument $policyDocument) => redirect(route('policy-documents.show', $policyDocument).'#new-version'))->name('policy-documents.versions.index');
Route::post('/policy-documents/{policyDocument}/versions', [PolicyDocumentController::class, 'storeVersion'])->name('policy-documents.versions.store');
Route::post('/portal-assistant/ask', [PortalAssistantController::class, 'ask'])->name('portal-assistant.ask');
Route::get('/reports/policy-circular', [PolicyDocumentController::class, 'reportCirculars'])->name('reports.circulars');
Route::get('/reports/policy-versions', [PolicyDocumentController::class, 'reportVersions'])->name('reports.versions');
Route::get('/reports/dashboard', [ReportingDashboardController::class, 'index'])->name('reports.dashboard');

Route::get('/roles', [RoleManagementController::class, 'index'])->name('roles.index');
Route::post('/roles', [RoleManagementController::class, 'store'])->name('roles.store');
Route::put('/roles/{user}', [RoleManagementController::class, 'update'])->name('roles.update');

Route::get('/topic-categories', [TopicCategoryController::class, 'index'])->name('topic-categories.index');
Route::post('/topic-categories', [TopicCategoryController::class, 'store'])->name('topic-categories.store');
Route::put('/topic-categories/{topicCategory}', [TopicCategoryController::class, 'update'])->name('topic-categories.update');
Route::delete('/topic-categories/{topicCategory}', [TopicCategoryController::class, 'destroy'])->name('topic-categories.destroy');
Route::post('/topic-categories/subtopics', [TopicCategoryController::class, 'storeSubtopic'])->name('topic-subtopics.store');
Route::put('/topic-categories/subtopics/{topicSubtopic}', [TopicCategoryController::class, 'updateSubtopic'])->name('topic-subtopics.update');
Route::delete('/topic-categories/subtopics/{topicSubtopic}', [TopicCategoryController::class, 'destroySubtopic'])->name('topic-subtopics.destroy');
Route::post('/topic-categories/details', [TopicCategoryController::class, 'storeDetail'])->name('topic-details.store');
Route::put('/topic-categories/details/{topicDetail}', [TopicCategoryController::class, 'updateDetail'])->name('topic-details.update');
Route::delete('/topic-categories/details/{topicDetail}', [TopicCategoryController::class, 'destroyDetail'])->name('topic-details.destroy');

Route::get('/lookup-values', [LookupValueController::class, 'index'])->name('lookup-values.index');
Route::post('/lookup-values', [LookupValueController::class, 'store'])->name('lookup-values.store');
Route::put('/lookup-values/{lookupValue}', [LookupValueController::class, 'update'])->name('lookup-values.update');
Route::delete('/lookup-values/{lookupValue}', [LookupValueController::class, 'destroy'])->name('lookup-values.destroy');
Route::get('/document-activity-logs', [DocumentActivityLogController::class, 'index'])->name('document-activity-logs.index');
Route::get('/reports/user-access', [UserAccessReportController::class, 'index'])->name('reports.user-access');
Route::get('/reports/user-access/export', [UserAccessReportController::class, 'export'])->name('reports.user-access.export');
Route::get('/directory-sync', [DirectorySyncController::class, 'index'])->name('directory-sync.index');
Route::post('/directory-sync', [DirectorySyncController::class, 'store'])->name('directory-sync.store');

Route::middleware(EnsureFormBuilderEnabled::class)->group(function (): void {
    Route::get('/form-templates', [FormTemplateController::class, 'index'])->name('form-templates.index');
    Route::post('/form-templates', [FormTemplateController::class, 'store'])->name('form-templates.store');
    Route::get('/form-templates/{formTemplate}/edit', [FormTemplateController::class, 'edit'])->name('form-templates.edit');
    Route::put('/form-templates/{formTemplate}', [FormTemplateController::class, 'update'])->name('form-templates.update');
    Route::delete('/form-templates/{formTemplate}', [FormTemplateController::class, 'destroy'])->name('form-templates.destroy');
    Route::post('/form-templates/{formTemplate}/fields', [FormTemplateController::class, 'storeField'])->name('form-templates.fields.store');
    Route::post('/form-templates/{formTemplate}/components', [FormTemplateController::class, 'quickAddField'])->name('form-templates.components.store');
    Route::put('/form-templates/{formTemplate}/fields/{formField}', [FormTemplateController::class, 'updateField'])->name('form-templates.fields.update');
    Route::delete('/form-templates/{formTemplate}/fields/{formField}', [FormTemplateController::class, 'destroyField'])->name('form-templates.fields.destroy');
    Route::post('/form-templates/{formTemplate}/fields/reorder', [FormTemplateController::class, 'reorderFields'])->name('form-templates.fields.reorder');
});
