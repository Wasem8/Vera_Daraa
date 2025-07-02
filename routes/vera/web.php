<?php

use App\Http\Controllers\Admin\AdminAuthController;

use App\Http\Controllers\Admin\UserManagementController;

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Receptionist\AuthController;
use App\Http\Controllers\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::post('/admin/login',[AdminAuthController::class,'adminLogin']);
Route::post('/receptionist/login',[AuthController::class,'receptionistLogin']);

Route::post('/forget-password',[ResetPasswordController::class,'userForgetPassword']);
Route::post('/check-code',[ResetPasswordController::class,'userCheckCode']);
Route::post('/reset-password',[ResetPasswordController::class,'userResetPassword']);



Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout',[AdminAuthController::class,'adminLogout']);

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
    Route::post('/', [DepartmentController::class, 'store'])->name('departments.store');
    Route::post('/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
});

Route::middleware(['auth:sanctum'])->prefix('admin/clients')->group(function () {
    Route::post('/{id}',[ProfileController::class,'adminEditClientProfile'])->name('clients.update');
    Route::get('/{id}/history',[ProfileController::class,'getClientHistory'])->name('clients.history');
});





