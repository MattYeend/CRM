<?php

use App\Models\JobTitle;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/users', function () {
        return Inertia::render('Users/Index', [
            'users' => User::with('role', 'jobTitle')->get()
        ]);
    })->name('users.index');

    Route::get('/users/create', function () {
        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
            'jobTitles' => JobTitle::all(),
        ]);
    })->name('users.create');

    Route::get('/users/{user}', function (User $user) {
        return Inertia::render('Users/Show', [
            'user' => $user->load('role', 'jobTitle')
        ]);
    })->name('users.show');

    Route::get('/users/{user}/edit', function (User $user) {
        return Inertia::render('Users/Update', [
            'user' => $user->load('role', 'jobTitle'),
            'roles' => Role::all(),
            'jobTitles' => JobTitle::all(),
        ]);
    })->name('users.edit');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
