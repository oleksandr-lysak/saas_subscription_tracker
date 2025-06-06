<?php

use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('subscriptions.index');
});

Route::resource('subscriptions', SubscriptionController::class);
