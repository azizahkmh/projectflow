<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Auth\AccessRequestController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleSimulationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes (pre-login index / landing page)
|--------------------------------------------------------------------------
*/
Route::view('/', 'landing')->name('landing');
Route::view('/features', 'landing')->name('features');
Route::view('/pricing', 'landing')->name('pricing');
Route::view('/about', 'landing')->name('about');

/*
|--------------------------------------------------------------------------
| Guest authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/request-access', [AccessRequestController::class, 'create'])->name('access.create');
    Route::post('/request-access', [AccessRequestController::class, 'store'])->name('access.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated application
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'simulate.role'])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // ----- Projects -----
    Route::resource('projects', ProjectController::class)->except(['create', 'edit']);

    // ----- Tasks -----
    Route::resource('tasks', TaskController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('/tasks/{task}/move', [TaskController::class, 'move'])->name('tasks.move');

    // ----- Orders -----
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    // ----- Finance & Expenses -----
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::post('/finance/expenses', [FinanceController::class, 'store'])->name('finance.expenses.store');
    Route::patch('/finance/expenses/{expense}/approve', [FinanceController::class, 'approve'])->name('finance.expenses.approve');
    Route::patch('/finance/expenses/{expense}', [FinanceController::class, 'update'])->name('finance.expenses.update');
    Route::delete('/finance/expenses/{expense}', [FinanceController::class, 'destroy'])->name('finance.expenses.destroy');

    // ----- Reports -----
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');


    // ----- Staff -----
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::patch('/staff/{user}/toggle', [StaffController::class, 'toggle'])->name('staff.toggle');
    Route::patch('/staff/{user}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->name('staff.destroy');

    // ----- Activity log -----
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');

    // ----- "Simulate role" dev switcher -----
    Route::put('/settings/simulate-role', [RoleSimulationController::class, 'update'])->name('simulate-role.update');
    Route::delete('/settings/simulate-role', [RoleSimulationController::class, 'reset'])->name('simulate-role.reset');

    // ----- Simple profile screen -----
    Route::view('/profile', 'profile.index')->name('profile.edit');
});
