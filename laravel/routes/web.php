<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Public Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,15')->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,15')->name('register.post');
Route::get('/verify-email/{user}', [AuthController::class, 'showVerification'])->name('verification.form');
Route::post('/verify-email/{user}', [AuthController::class, 'verify'])->middleware('throttle:10,15')->name('verification.verify');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [HomeController::class, 'index'])->name('home');

// Authenticated Routes
Route::middleware('auth')->group(function () {

    Route::middleware('role:CUSTOMER')->group(function () {
        Route::get('/customer-dashboard', [CustomerPortalController::class, 'dashboard'])->name('customer.dashboard');
        Route::get('/customer-appointment-availability', [CustomerPortalController::class, 'availableTimes'])->name('customer.appointments.availability');
        Route::post('/customer-appointments', [CustomerPortalController::class, 'book'])->name('customer.appointments.book');
        Route::patch('/customer-appointments/{appointment}/cancel', [CustomerPortalController::class, 'cancel'])->name('customer.appointments.cancel');
        Route::patch('/customer-appointments/{appointment}/reschedule', [CustomerPortalController::class, 'reschedule'])->name('customer.appointments.reschedule');
        Route::put('/customer-profile', [CustomerPortalController::class, 'updateProfile'])->name('customer.profile.update');
        Route::put('/customer-password', [CustomerPortalController::class, 'updatePassword'])->name('customer.password.update');
        Route::post('/customer-ratings/website', [RatingController::class, 'storeWebsite'])->name('customer.ratings.website');
        Route::post('/customer-ratings/services', [RatingController::class, 'storeService'])->name('customer.ratings.service');
    });

    Route::middleware('role:OWNER,STAFF,ADMIN')->group(function () {
        Route::get('/employee-dashboard', [EmployeeDashboardController::class, 'index'])->middleware('role:STAFF')->name('employee.dashboard');

        // Appointments (Owner, Staff, Admin)
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
        Route::patch('/appointments/{appointment}/arrival', [AppointmentController::class, 'updateArrivalStatus'])->name('appointments.arrival');
        Route::patch('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

        // Sales / POS (Owner, Staff, Admin)
        Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
        Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
        Route::get('/sales/{sale}/receipt', [SalesController::class, 'receipt'])->name('sales.receipt');

        // Customers (Owner, Staff, Admin)
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // Services (Owner, Staff, Admin)
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

        // Inventory (Owner, Staff, Admin)
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/logs', [InventoryController::class, 'index'])->name('inventory.logs');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::post('/inventory/{inventory}/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');
        Route::put('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    });

    // 6. Loyalty (Owner, Staff, Admin)
    Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
    Route::post('/loyalty/settings', [LoyaltyController::class, 'updateSettings'])->name('loyalty.settings');
    Route::post('/loyalty/rewards/{reward}/redeem', [LoyaltyController::class, 'redeem'])->name('loyalty.redeem');

    // 7. Notifications (Owner, Staff, Admin)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // --- OWNER & ADMIN MODULES ---
    Route::middleware('role:OWNER,ADMIN')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Employees
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::patch('/employees/{employee}/schedule', [EmployeeController::class, 'updateSchedule'])->name('employees.schedule');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

        // Expenses
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        // Analytics & Reports
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');

        // AI Assistant
        Route::get('/ai-assistant', [AiAssistantController::class, 'index'])->name('ai.index');
        Route::post('/ai-assistant/ask', [AiAssistantController::class, 'ask'])->name('ai.ask');

        // Audit Logs & Settings
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/service-ratings', [RatingController::class, 'serviceRatings'])->middleware('role:OWNER')->name('service-ratings.index');
    });

    // --- ADMIN MODULES ---
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/admin-dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('/admin/users', [AdminDashboardController::class, 'storeUser'])->name('admin.users.store');
        Route::patch('/admin/users/{user}/toggle-status', [AdminDashboardController::class, 'toggleUserStatus'])->name('admin.users.toggle');
        Route::post('/admin/users/{user}/reset-password', [AdminDashboardController::class, 'resetPassword'])->name('admin.users.resetPassword');
        Route::get('/admin/website-ratings', [RatingController::class, 'websiteRatings'])->name('admin.website-ratings.index');
    });

});
