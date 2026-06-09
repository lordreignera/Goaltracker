<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\QuarterController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Goals\GoalController;
use App\Http\Controllers\Goals\ObjectiveController;
use App\Http\Controllers\Goals\SupervisorReviewController;
use App\Http\Controllers\Goals\WeeklyUpdateController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('quarters', QuarterController::class)->only(['index', 'store']);
    Route::get('users/management', [UserApprovalController::class, 'index'])->name('users.management.index');
    Route::get('roles/management', [UserApprovalController::class, 'roles'])->name('roles.management.index');
    Route::post('roles/management', [UserApprovalController::class, 'storeRole'])->name('roles.management.store');
    Route::put('roles/management/{role}/permissions', [UserApprovalController::class, 'updateRolePermissions'])->name('roles.management.permissions.update');
    Route::put('users/management/{user}', [UserApprovalController::class, 'update'])->name('users.management.update');
    Route::post('users/management/{user}/approve', [UserApprovalController::class, 'approve'])->name('users.management.approve');
    Route::post('users/management/{user}/reject', [UserApprovalController::class, 'reject'])->name('users.management.reject');
    Route::delete('users/management/{user}', [UserApprovalController::class, 'destroy'])->name('users.management.destroy');
    Route::get('users/approvals', [UserApprovalController::class, 'index'])->name('users.approvals.index');
    Route::post('users/approvals/{user}/approve', [UserApprovalController::class, 'approve'])->name('users.approvals.approve');
    Route::post('users/approvals/{user}/reject', [UserApprovalController::class, 'reject'])->name('users.approvals.reject');
    Route::resource('goals', GoalController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('goals/{goal}/submit', [GoalController::class, 'submit'])->name('goals.submit');
    Route::post('goals/{goal}/objectives', [ObjectiveController::class, 'store'])->name('goals.objectives.store');
    Route::post('objectives/{objective}/weekly-updates', [WeeklyUpdateController::class, 'store'])->name('objectives.weekly-updates.store');
    Route::post('weekly-updates/{weeklyUpdate}/reviews', [SupervisorReviewController::class, 'store'])->name('weekly-updates.reviews.store');
});
