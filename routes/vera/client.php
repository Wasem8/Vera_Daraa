<?php


use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Booking\BookingController;
use App\Http\Controllers\Client\FavouriteController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FaceAnalysisController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Middleware\VerifiedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');











Route::post('/register',[AuthController::class,'register']);
Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'customVerify'])
    ->name('custom.verification.verify');

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
    Route::get('/services',[ServiceController::class,'index']);
    Route::get('/service/{id}',[ServiceController::class,'ShowService']);
    Route::post('/search-services',[ServiceController::class,'searchServices']);

    ////Departments
    Route::get('/departments',[DepartmentController::class,'index']);
    Route::get('/department/{id}',[DepartmentController::class,'show']);
    Route::get('/department/{departmentId}/services',[DepartmentController::class,'servicesDepartment']);

    ///Booking
    Route::post('/booking',[BookingController::class,'booking']);
    Route::post('/update-booking/{bookingId}',[BookingController::class,'updateBooking']);
    Route::get('/get-bookings',[BookingController::class,'getBookings']);
    Route::get('/get-booking/{id}',[BookingController::class,'getBooking']);
    Route::delete('delete-booking/{id}',[BookingController::class,'deleteBooking']);
    Route::post('available',[BookingController::class,'getAvailableSlots']);

    //Favourites
    Route::get('/add-favourite/{id}',[FavouriteController::class,'addFavourite']);
    Route::delete('/remove-favourite/{id}',[FavouriteController::class,'removeFavourite']);
    Route::get('/favourite/{id}',[FavouriteController::class,'favourite']);
    Route::get('/favourites',[FavouriteController::class,'favourites']);


    //offers
    Route::get('/offer/{id}',[OfferController::class,'show']);
    Route::post('/offers',[OfferController::class,'index']);


    Route::post('/analyze-face', [FaceAnalysisController::class, 'analyze']);
});




