<?php


use App\Http\Controllers\Ai\FaceAnalysisController;
use App\Http\Controllers\Auth\ClientAuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Booking\ClientBookingController;
use App\Http\Controllers\Payment\InvoiceController;
use App\Http\Controllers\Service\DepartmentController;
use App\Http\Controllers\Service\FavouriteController;
use App\Http\Controllers\Service\OfferController;
use App\Http\Controllers\Service\ServiceController;
use App\Http\Controllers\System\NotificationController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Middleware\VerifiedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




Route::post('/register',[ClientAuthController::class,'register']);
Route::get('/verify-email/{id}/{hash}', [ClientAuthController::class, 'customVerify'])
    ->name('custom.verification.verify');

Route::post('/email/verification-notification', [ClientAuthController::class, 'resendEmail'])->middleware( 'throttle:6,1');
Route::post('/login',[ClientAuthController::class,'clientLogin']);
Route::post('/forget-password',[ResetPasswordController::class,'userForgetPassword']);
Route::post('/check-code',[ResetPasswordController::class,'userCheckCode']);
Route::post('/reset-password',[ResetPasswordController::class,'userResetPassword']);



Route::group(['middleware' => ['auth:sanctum',VerifiedEmail::class,'active','role:client']], function () {
    Route::post('/logout',[ClientAuthController::class,'clientLogout']);

    Route::post('edit-profile',[ProfileController::class,'editProfile']);
    Route::get('/profile',[ProfileController::class,'showProfile']);

    ////services
    Route::resource('services', ServiceController::class)->only(['index', 'show']);
    Route::post('/search-services',[ServiceController::class,'searchServices']);

    ////Departments
    Route::resource('departments',DepartmentController::class)->only(['index','show']);
    Route::get('/department/{departmentId}/services',[DepartmentController::class,'servicesDepartment']);

    ///Booking
    Route::resource('bookings',ClientBookingController::class);
    Route::post('available',[ClientBookingController::class,'availableSlots']);

    //Favourites
    Route::resource('favourites',FavouriteController::class)->except('store','update');
    Route::get('/add-favourite/{id}',[FavouriteController::class,'store']);



    //offers
    Route::resource('offers',OfferController::class)->only(['index','show']);


    //invoices
    Route::get('/invoices',[InvoiceController::class,'clientInvoices']);
    Route::get('/invoice/{id}',[InvoiceController::class,'clientInvoice']);


    Route::post('/analyze-face', [FaceAnalysisController::class, 'analyze']);


    //notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('delete-notification/{id}', [NotificationController::class, 'destroy']);
});




