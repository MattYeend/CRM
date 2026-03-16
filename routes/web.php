<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Route::get('/users', function () {
//     return Inertia::render('Users/Index', [
//         'users' => User::with('role', 'jobTitle')->get()
//     ]);
// })->middleware(['auth', 'verified']);
// Route::get('/users/create', function () {
//     return Inertia::render('Users/Create');
// })->middleware(['auth', 'verified']);
// Route::get('/users/update', function () {
//     return Inertia::render('Users/Update');
// })->middleware(['auth', 'verified']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
