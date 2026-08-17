<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('lms')->name('lms.')->group(function () {
    Route::get('/dashboard', function () {
        return response()->json([
            'user' => auth()->user(),
            'session_id' => session()->getId(),
        ]);
    });

});
