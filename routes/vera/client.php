<?php


use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Middleware\VerifiedEmail;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Responses\Response;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');











Route::post('/register',[AuthController::class,'register']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware( 'signed')->name('verification.verify');
Route::post('/email/verification-notification', [AuthController::class, 'resendEmail'])->middleware( 'throttle:6,1');
Route::post('/login',[AuthController::class,'clientLogin']);
Route::post('/forget-password',[ResetPasswordController::class,'userForgetPassword']);
Route::post('/check-code',[ResetPasswordController::class,'userCheckCode']);
Route::post('/reset-password',[ResetPasswordController::class,'userResetPassword']);



Route::group(['middleware' => ['auth:sanctum',VerifiedEmail::class]], function () {
    Route::post('/logout',[AuthController::class,'clientLogout']);
});




