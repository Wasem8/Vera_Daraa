<?php

use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\ReceptionistAuthController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Booking\BookingController;
use App\Http\Controllers\Booking\WebBookingController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InvoiceArchivedController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StatisticsController;
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

    //payments
    Route::get('/payments', [PaymentController::class, 'payments'])->name('payments.index');
    Route::get('/payment/{id}', [PaymentController::class, 'payment'])->name('payment.index');
    Route::post('/invoice/{id}/payment', [PaymentController::class, 'store'])->name('payment.store');


    ///Services
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/add-service', [ServiceController::class, 'store'])->name('service.store');
    Route::post('update-service/{service}', [ServiceController::class, 'update'])->name('service.update');
    Route::delete('delete-service/{service}', [ServiceController::class, 'destroy'])->name('service.destroy');
    Route::get('/service/{id}',[ServiceController::class,'ShowService']);
    Route::post('/search-service',[ServiceController::class,'searchServices']);

    //offers
    Route::post('/add-offer',[OfferController::class,'store'])->name('offer.store');
    Route::get('/offer/{id}',[OfferController::class,'show'])->name('offer.show');
    Route::post('/offers',[OfferController::class,'index'])->name('offer.index');
    Route::post('/update-offer/{id}',[OfferController::class,'update'])->name('offer.update');


    //Invoice
    Route::post('invoice/{booking}',[InvoiceController::class,'store']);
    Route::get('/invoices',[InvoiceController::class,'index'])->name('invoices.index');
    Route::get('/invoice/{id}',[InvoiceController::class,'show'])->name('invoice.show');
    Route::post('reports',[InvoiceController::class,'financialReports'])->name('reports.index');

    //archive
    Route::get('/invoice-archive/{id}',[InvoiceArchivedController::class,'archive'])->name('invoice.archive');
    Route::get('restore-invoice/{id}',[InvoiceArchivedController::class,'restoreInvoice'])->name('invoice.restore');
    Route::get('archives',[InvoiceArchivedController::class,'index'])->name('invoices.archives');
    Route::get('archive/{id}',[InvoiceArchivedController::class,'show'])->name('invoices.show');


    ///booking
    Route::post('/store-booking',[WebBookingController::class,'storeBooking']);
    Route::get('/booking-approve/{booking}',[WebBookingController::class,'bookingApprove']);
    Route::get('/booking-reject/{booking}',[WebBookingController::class,'bookingReject']);
    Route::get('/daily/{data?}',[WebBookingController::class,'dailyBooking']);
    Route::get('canceled-booking/{booking}',[WebBookingController::class,'canceledBooking']);
    Route::get('/archive-booking/{booking}',[WebBookingController::class,'archive']);
    Route::get('/un-archive/{booking}',[WebBookingController::class,'unArchive']);
    Route::post('update-booking',[WebBookingController::class,'updateBooking']);
    Route::post('/available',[WebBookingController::class,'availableSlots']);


    ///statistics
    Route::post('/num-clients',[StatisticsController::class,'numberClients']);
    Route::get('popular-services',[StatisticsController::class,'mostPopularServices']);
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/user',[UserManagementController::class,'storeUser']);
    Route::get('/users',[UserManagementController::class,'index']);
    Route::get('/user/{id}',[UserManagementController::class,'show']);
    Route::post('/users/{user}/toggle-status',[UserManagementController::class,'toggleStatus']);
    Route::post('/search-user',[UserManagementController::class,'searchUser']);
});

Route::middleware(['auth:sanctum'])->prefix('admin/employees')->group(function () {

    Route::get('/', [EmployeeController::class, 'index'])->name('admin.employees.index');
    Route::post('/', [EmployeeController::class, 'store'])->name('admin.employees.store');
    Route::put('/{employee}', [EmployeeController::class, 'update'])->name('admin.employees.update');
    Route::get('/search',[EmployeeController::class, 'search'])->name('admin.employees.search');
   Route::post('/{id}/toggle-archive',[EmployeeController::class, 'archive'])->name('admin.employees.archive');
   Route::get('archives',[EmployeeController::class,'showArchive']);
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





