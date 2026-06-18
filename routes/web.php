<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\CompanySettingController;
use App\Http\Controllers\Admin\QuarterController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Goals\GoalController;
use App\Http\Controllers\Goals\ObjectiveController;
use App\Http\Controllers\Goals\SupervisorReviewController;
use App\Http\Controllers\Goals\WeeklyUpdateController;
use App\Http\Controllers\Reports\QuarterlyReportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('sections', SectionController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('quarters', QuarterController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('settings/company', [CompanySettingController::class, 'edit'])->name('settings.company.edit');
    Route::put('settings/company', [CompanySettingController::class, 'update'])->name('settings.company.update');
    Route::get('users/management', [UserManagementController::class, 'index'])->name('users.management.index');
    Route::get('roles/management', [RoleManagementController::class, 'index'])->name('roles.management.index');
    Route::post('roles/management', [RoleManagementController::class, 'store'])->name('roles.management.store');
    Route::put('roles/management/{role}/permissions', [RoleManagementController::class, 'updatePermissions'])->name('roles.management.permissions.update');
    Route::put('users/management/{user}', [UserManagementController::class, 'update'])->name('users.management.update');
    Route::post('users/management/{user}/approve', [UserManagementController::class, 'approve'])->name('users.management.approve');
    Route::post('users/management/{user}/reject', [UserManagementController::class, 'reject'])->name('users.management.reject');
    Route::delete('users/management/{user}', [UserManagementController::class, 'destroy'])->name('users.management.destroy');
    Route::get('users/approvals', [UserManagementController::class, 'index'])->name('users.approvals.index');
    Route::post('users/approvals/{user}/approve', [UserManagementController::class, 'approve'])->name('users.approvals.approve');
    Route::post('users/approvals/{user}/reject', [UserManagementController::class, 'reject'])->name('users.approvals.reject');
    Route::resource('goals', GoalController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
    Route::post('goals/{goal}/submit', [GoalController::class, 'submit'])->name('goals.submit');
    Route::post('goals/{goal}/objectives', [ObjectiveController::class, 'store'])->name('goals.objectives.store');
    Route::post('objectives/{objective}/weekly-updates', [WeeklyUpdateController::class, 'store'])->name('objectives.weekly-updates.store');
    Route::put('weekly-updates/{weeklyUpdate}', [WeeklyUpdateController::class, 'update'])->name('weekly-updates.update');
    Route::post('weekly-updates/{weeklyUpdate}/reviews', [SupervisorReviewController::class, 'store'])->name('weekly-updates.reviews.store');
    Route::get('reports/quarterly', [QuarterlyReportController::class, 'index'])->name('reports.quarterly.index');
    Route::get('reports/quarterly/pdf', [QuarterlyReportController::class, 'pdf'])->name('reports.quarterly.pdf');
});
