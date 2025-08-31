<?php

use Illuminate\Support\Facades\Route;

// -------------------- Public Routes --------------------
Route::get('/', fn() => view('welcome'));

// -------------------- Auth Routes ----------------------
Route::middleware(['auth:basic'])->group(function () { });


require __DIR__ . '/client.php';

require __DIR__ . '/admin.php';

require __DIR__ . '/user.php';

require __DIR__ . '/auth.php';

