<?php


use App\Http\Responses\Response;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');






Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    event(new Verified(User::query()->findOrFail($request->route('id'))));
    return Response::success('true','Email verified');
})->middleware(['auth:sanctum', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return Response::success(true,'Verification link sent!  ');
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');


