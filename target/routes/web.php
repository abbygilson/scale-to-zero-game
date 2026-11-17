<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// The scale-to-zero game times the first request to this route, so it skips
// the web middleware: sessions and cookies would add to the measured time.
Route::match(['GET', 'HEAD'], '/wake', fn () => response()->noContent()->withHeaders([
    'Access-Control-Allow-Origin' => '*',
    'Cache-Control' => 'no-store, max-age=0',
    'Timing-Allow-Origin' => '*',
]))->withoutMiddleware('web')->name('wake');
