<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\AdminPropertyController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\BuyerController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\FavoriteController;

// Public routes
Route::prefix('public')->group(function () {
    Route::get('properties', [PublicController::class, 'properties']);
    Route::get('properties/featured', [PublicController::class, 'featuredProperties']);
    Route::get('properties/{slug}', [PublicController::class, 'propertyDetail']);
    Route::get('cities', [PublicController::class, 'cities']);
    Route::get('areas', [PublicController::class, 'areas']);
    Route::get('property-types', [PublicController::class, 'propertyTypes']);
    Route::get('amenities', [PublicController::class, 'amenities']);
    Route::get('stats', [PublicController::class, 'stats']);
    Route::post('contact', [PublicController::class, 'contact']);
});

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth:sanctum');
    Route::put('user', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
});

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Seller routes
    Route::prefix('seller')->middleware('role:seller')->group(function () {
        Route::get('dashboard', [SellerController::class, 'dashboard']);
        Route::get('properties', [SellerController::class, 'properties']);
        Route::post('properties', [SellerController::class, 'store']);
        Route::get('deals', [SellerController::class, 'deals']);
        Route::get('appointments', [SellerController::class, 'appointments']);
    });

    // Buyer routes
    Route::prefix('buyer')->middleware('role:buyer')->group(function () {
        Route::get('dashboard', [BuyerController::class, 'dashboard']);
        Route::get('appointments', [BuyerController::class, 'appointments']);
        Route::post('appointments', [BuyerController::class, 'requestAppointment']);
        Route::get('deals', [BuyerController::class, 'deals']);
        Route::get('favorites', [FavoriteController::class, 'index']);
        Route::post('favorites/{property}', [FavoriteController::class, 'store']);
        Route::delete('favorites/{property}', [FavoriteController::class, 'destroy']);
    });

    // Admin routes
    Route::prefix('admin')->middleware('role:super_admin,staff')->group(function () {
        Route::get('dashboard/stats', [AdminDashboardController::class, 'stats']);
        Route::get('dashboard/recent-activity', [AdminDashboardController::class, 'recentActivity']);
        Route::get('dashboard/properties-by-status', [AdminDashboardController::class, 'propertiesByStatus']);
        Route::get('dashboard/deals-by-stage', [AdminDashboardController::class, 'dealsByStage']);
        Route::get('dashboard/monthly-commission', [AdminDashboardController::class, 'monthlyCommission']);

        Route::get('properties', [AdminPropertyController::class, 'index']);
        Route::get('properties/pending', [AdminPropertyController::class, 'pending']);
        Route::post('properties/{property}/verify', [AdminPropertyController::class, 'verify']);
        Route::post('properties/{property}/toggle-featured', [AdminPropertyController::class, 'toggleFeatured']);

        Route::get('sellers', [AdminUserController::class, 'sellers']);
        Route::get('buyers', [AdminUserController::class, 'buyers']);
        Route::patch('users/{user}/status', [AdminUserController::class, 'updateUserStatus']);

        Route::get('appointments', [AppointmentController::class, 'index']);
        Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);
        Route::patch('appointments/{appointment}', [AppointmentController::class, 'update']);
        Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'update']);
        Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy']);

        Route::get('deals', [DealController::class, 'index']);
        Route::get('deals/{deal}', [DealController::class, 'show']);
        Route::post('deals', [DealController::class, 'store']);
        Route::patch('deals/{deal}/stage', [DealController::class, 'updateStage']);
        Route::post('deals/{deal}/commission', [DealController::class, 'recordCommission']);

        Route::get('contacts', [ContactController::class, 'index']);
        Route::get('contacts/{contact}', [ContactController::class, 'show']);
        Route::delete('contacts/{contact}', [ContactController::class, 'destroy']);

        Route::get('documents', [DocumentController::class, 'index']);
        Route::post('documents', [DocumentController::class, 'store']);
        Route::delete('documents/{document}', [DocumentController::class, 'destroy']);
    });

    // Shared authenticated user routes
    Route::get('properties', [PropertyController::class, 'index']);
    Route::post('properties', [PropertyController::class, 'store'])->middleware('role:seller,super_admin,staff');
    Route::get('properties/{property}', [PropertyController::class, 'show']);
    Route::put('properties/{property}', [PropertyController::class, 'update']);
    Route::patch('properties/{property}', [PropertyController::class, 'update']);
    Route::delete('properties/{property}', [PropertyController::class, 'destroy']);
    Route::post('properties/{property}/images', [PropertyController::class, 'uploadImages']);

    Route::get('appointments', [AppointmentController::class, 'index']);
    Route::post('appointments', [AppointmentController::class, 'store']);

    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites/{property}', [FavoriteController::class, 'store']);
    Route::delete('favorites/{property}', [FavoriteController::class, 'destroy']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
});
