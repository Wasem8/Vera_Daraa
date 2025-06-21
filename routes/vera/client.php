<?php


use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\BookingController;
use App\Http\Controllers\Client\DepartmentController;
use App\Http\Controllers\Client\FavouriteController;
use App\Http\Controllers\Client\ServiceController;
use App\Http\Controllers\ProfileController;
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
    Route::post('edit-profile',[ProfileController::class,'editProfile']);
    Route::get('/profile',[ProfileController::class,'showProfile']);
    ////services
    Route::get('/services',[ServiceController::class,'ShowServices']);
    Route::get('/service/{id}',[ServiceController::class,'ShowService']);
    Route::post('/search-services',[ServiceController::class,'searchServices']);
    ////Departments
    Route::get('/departments',[DepartmentController::class,'ShowDepartments']);
    Route::get('/department/{id}',[DepartmentController::class,'ShowDepartment']);
    Route::get('/services/{id}',[DepartmentController::class,'showServices']);
    ///Booking
    Route::post('/booking',[BookingController::class,'booking']);
    Route::post('/update-booking',[BookingController::class,'updateBooking']);
    Route::get('/get-bookings',[BookingController::class,'getBookings']);
    Route::get('/get-booking/{id}',[BookingController::class,'getBooking']);
    //Favourites
    Route::post('/add-favourite/{id}',[FavouriteController::class,'addFavourite']);
    Route::post('/remove-favourite/{id}',[FavouriteController::class,'removeFavourite']);
    Route::post('/favourite/{id}',[FavouriteController::class,'favourite']);
    Route::post('/favourites',[FavouriteController::class,'favourites']);

});




