<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reboot', function () {
    // Clear all caches and compiled files
    Artisan::call('optimize:clear');

    // Run pending migrations
    Artisan::call('migrate', ['--force' => true]);

    // Rebuild caches for performance
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');

    return response()->json([
        'message' => 'System rebooted, migrations executed, and caches cleared & rebuilt successfully!',
        'timestamp' => now()->toDateTimeString(),
    ]);
});

Route::get('/orders/{order}', [OrderController::class, 'show_order'])->name('orders.show');

Route::get('/{any}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '.*');
