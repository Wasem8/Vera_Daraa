<?php

use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\ReceptionistAuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::post('/admin/login',[AdminAuthController::class,'adminLogin']);
Route::post('/receptionist/login',[ReceptionistAuthController::class,'receptionistLogin']);

Route::post('/forget-password',[ResetPasswordController::class,'userForgetPassword']);
Route::post('/check-code',[ResetPasswordController::class,'userCheckCode']);
Route::post('/reset-password',[ResetPasswordController::class,'userResetPassword']);



Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout',[AdminAuthController::class,'adminLogout']);

    Route::get('/payments', [PaymentController::class, 'payments'])->name('payments.index');
    Route::get('/payment/{id}', [PaymentController::class, 'payment'])->name('payment.index');
    Route::post('/bookings/{id}/payment', [PaymentController::class, 'store'])->name('payment.store');

    ///Services
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/add-service', [ServiceController::class, 'store'])->name('service.store');
    Route::post('update-service/{service}', [ServiceController::class, 'update'])->name('service.update');
    Route::delete('delete-service/{service}', [ServiceController::class, 'destroy'])->name('service.destroy');
    Route::get('/service/{id}',[ServiceController::class,'ShowService']);
    Route::post('/search-service',[ServiceController::class,'searchServices']);

});

Route::group(['middleware' => ['auth:sanctum', 'active']], function () {
    Route::get('/users',[UserManagementController::class,'index']);
    Route::post('/users/{user}/toggle-status',[UserManagementController::class,'toggleStatus']);
});

Route::middleware(['auth:sanctum'])->prefix('admin/employees')->group(function () {

    Route::get('/', [EmployeeController::class, 'index'])->name('admin.employees.index');
    Route::post('/', [EmployeeController::class, 'store'])->name('admin.employees.store');
    Route::post('/{employee}', [EmployeeController::class, 'update'])->name('admin.employees.update');
    Route::get('/search',[EmployeeController::class, 'search'])->name('admin.employees.search');
   Route::post('/{id}/toggle-archive',[EmployeeController::class, 'archive'])->name('admin.employees.archive');
});

Route::middleware(['auth:sanctum'])->prefix('admin/departments')->group(function () {
    Route::get('/', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/{department}', [DepartmentController::class, 'show'])->name('departments.show');
    Route::post('/', [DepartmentController::class, 'store'])->name('departments.store');
    Route::post('/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
});



Route::middleware(['auth:sanctum'])->prefix('admin/clients')->group(function () {
    Route::post('/{id}',[ProfileController::class,'adminEditClientProfile'])->name('clients.update');
    Route::get('/{id}/history',[ProfileController::class,'getClientHistory'])->name('clients.history');
});





