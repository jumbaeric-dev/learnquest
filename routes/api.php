<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// IntaSend server-to-server webhook. Authentication uses the configured challenge plus API status verification.
Route::post('/webhooks/intasend', \App\Http\Controllers\Webhooks\IntaSendWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('webhooks.intasend');

// Paystack signed server-to-server webhook. Settlement is confirmed via transaction verification.
Route::post('/webhooks/paystack', \App\Http\Controllers\Webhooks\PaystackWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('webhooks.paystack');

// PayPal signed server-to-server webhook. The controller verifies the transmission and reconciles via Orders v2.
Route::post('/webhooks/paypal', \App\Http\Controllers\Webhooks\PayPalWebhookController::class)
    ->middleware('throttle:60,1')
    ->name('webhooks.paypal');
