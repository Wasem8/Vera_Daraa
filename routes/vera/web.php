<?php

use App\Http\Controllers\Admin\AdminAuthController;
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



