<?php

use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\BuyerDashboardController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FavoriteWebController;
use App\Http\Controllers\Web\PublicPageController;
use App\Http\Controllers\Web\SellerDashboardController;
use App\Http\Controllers\Web\WebAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/properties', [PublicPageController::class, 'index'])->name('properties.index');
Route::get('/properties/{slug}', [PublicPageController::class, 'show'])->name('properties.show');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicPageController::class, 'submitContact'])->name('contact.submit');

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->name('register.attempt');
});

Route::post('/logout', [WebAuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/favorites/{property}', [FavoriteWebController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{property}', [FavoriteWebController::class, 'destroy'])->name('favorites.destroy');

    Route::prefix('dashboard/seller')->name('seller.')->middleware('role:seller')->group(function () {
        Route::get('/', [SellerDashboardController::class, 'index'])->name('dashboard');
        Route::post('/properties', [SellerDashboardController::class, 'storeProperty'])->name('properties.store');
        Route::patch('/properties/{property}', [SellerDashboardController::class, 'updateProperty'])->name('properties.update');
        Route::delete('/properties/{property}', [SellerDashboardController::class, 'destroyProperty'])->name('properties.destroy');
        Route::post('/properties/{property}/images', [SellerDashboardController::class, 'uploadImages'])->name('properties.images');
        Route::patch('/appointments/{appointment}', [SellerDashboardController::class, 'updateAppointment'])->name('appointments.update');
    });

    Route::prefix('dashboard/buyer')->name('buyer.')->middleware('role:buyer')->group(function () {
        Route::get('/', [BuyerDashboardController::class, 'index'])->name('dashboard');
        Route::post('/appointments', [BuyerDashboardController::class, 'requestAppointment'])->name('appointments.store');
        Route::patch('/appointments/{appointment}/cancel', [BuyerDashboardController::class, 'cancelAppointment'])->name('appointments.cancel');
    });

    Route::prefix('dashboard/admin')->name('admin.')->middleware('role:super_admin,staff')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/properties/{property}/verify', [AdminDashboardController::class, 'verifyProperty'])->name('properties.verify');
        Route::post('/properties/{property}/featured', [AdminDashboardController::class, 'toggleFeatured'])->name('properties.featured');
        Route::delete('/properties/{property}', [AdminDashboardController::class, 'destroyProperty'])->name('properties.destroy');
        Route::patch('/users/{user}/status', [AdminDashboardController::class, 'updateUserStatus'])->name('users.status');
        Route::patch('/appointments/{appointment}', [AdminDashboardController::class, 'updateAppointment'])->name('appointments.update');
        Route::patch('/deals/{deal}/stage', [AdminDashboardController::class, 'updateDealStage'])->name('deals.stage');
        Route::post('/deals/{deal}/commission', [AdminDashboardController::class, 'recordCommission'])->name('deals.commission');
        Route::patch('/contacts/{contact}/read', [AdminDashboardController::class, 'markContactRead'])->name('contacts.read');
        Route::delete('/contacts/{contact}', [AdminDashboardController::class, 'destroyContact'])->name('contacts.destroy');
        Route::post('/documents', [AdminDashboardController::class, 'storeDocument'])->name('documents.store');
        Route::delete('/documents/{document}', [AdminDashboardController::class, 'destroyDocument'])->name('documents.destroy');
    });
});
