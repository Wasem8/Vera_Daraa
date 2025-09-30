<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\ReceptionistAuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Booking\WebBookingController;
use App\Http\Controllers\Payment\InvoiceArchivedController;
use App\Http\Controllers\Payment\InvoiceController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Service\DepartmentController;
use App\Http\Controllers\Service\OfferController;
use App\Http\Controllers\Service\ServiceController;
use App\Http\Controllers\System\NotificationController;
use App\Http\Controllers\System\StatisticsController;
use App\Http\Controllers\User\EmployeeController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::post('/admin/login',[AdminAuthController::class,'adminLogin']);
Route::post('/receptionist/login',[ReceptionistAuthController::class,'receptionistLogin']);
Route::post('/forget-password',[ResetPasswordController::class,'userForgetPassword']);
Route::post('/check-code',[ResetPasswordController::class,'userCheckCode']);
Route::post('/reset-password',[ResetPasswordController::class,'userResetPassword']);


//Admin + receptionist
Route::group(['middleware' => ['auth:sanctum','active','role:admin|receptionist']], function (){

    ///booking
    Route::resource('bookings',WebBookingController::class);
    Route::get('/booking-approve/{booking}',[WebBookingController::class,'bookingApprove']);
    Route::get('/booking-reject/{booking}',[WebBookingController::class,'bookingReject']);
    Route::get('/daily/{data?}',[WebBookingController::class,'dailyBooking']);
    Route::get('/archive-booking/{booking}',[WebBookingController::class,'archive']);
    Route::get('/un-archive/{booking}',[WebBookingController::class,'unArchive']);
    Route::post('/available',[WebBookingController::class,'getAvailableSlots']);

    //Invoice
    Route::resource('invoices', InvoiceController::class)->except(['store']);
    Route::prefix('invoices')->group(function () {
        Route::post('{booking}', [InvoiceController::class, 'store']);
        Route::get('{invoice}/archive', [InvoiceArchivedController::class, 'archive']);
        Route::get('{invoice}/restore', [InvoiceArchivedController::class, 'restoreInvoice']);
        Route::resource('archives', InvoiceArchivedController::class)->except(['store']);
    });



  //payments
    Route::get('/payments', [PaymentController::class, 'payments'])->name('payments.index');
    Route::get('/payment/{id}', [PaymentController::class, 'payment'])->name('payment.index');
    Route::post('/invoice/{id}/payment', [PaymentController::class, 'store'])->name('payment.store');



    //users
    Route::resource('users',UserManagementController::class);
    Route::post('/search-user',[UserManagementController::class,'searchUser']);


    ///statistics
    Route::post('/num-clients',[StatisticsController::class,'numberClients']);
    Route::get('popular-services',[StatisticsController::class,'mostPopularServices']);
    Route::post('reports',[StatisticsController::class,'financialReports'])->name('reports.index');

    //Notification
    Route::get('/notification',[NotificationController::class,'adminNotification']);

});


//Admin

Route::group(['middleware' => ['auth:sanctum','active','role:admin']], function () {

    ///Services
    Route::resource('services',ServiceController::class);


    //departments
    Route::resource('departments',DepartmentController::class);


    //offer
    Route::resource('offers', OfferController::class);

    //user
    Route::post('/users/{user}/toggle-status',[UserManagementController::class,'toggleStatus']);

    //employee
    Route::resource('employees',EmployeeController::class);
    Route::get('/search',[EmployeeController::class, 'search'])->name('admin.employees.search');
    Route::post('/{id}/toggle-archive',[EmployeeController::class, 'archive'])->name('admin.employees.archive');
    Route::get('archives',[EmployeeController::class,'showArchive']);


});

Route::group(['middleware' => ['auth:sanctum','active']], function () {

    Route::post('/logout',[AdminAuthController::class,'adminLogout']);

    Route::resource('services', ServiceController::class)->only(['index','show']);
    Route::resource('departments', DepartmentController::class)->only(['index','show']);

});








