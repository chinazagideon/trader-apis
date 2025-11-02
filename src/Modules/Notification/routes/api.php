<?php

use App\Modules\Notification\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->name('notifications.')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        // Get notifications for an entity
        Route::get('/', [NotificationController::class, 'index'])->name('index');

        // Get unread notifications
        Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');

        // Get unread count
        Route::get('/unread/count', [NotificationController::class, 'unreadCount'])->name('unread.count');

        // Mark notification as read
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');

        // Mark all as read for entity
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');

        // Retry failed notification
        Route::post('/{id}/retry', [NotificationController::class, 'retry'])->name('retry');
    });
});
