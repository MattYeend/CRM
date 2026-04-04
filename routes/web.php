<?php

use App\Models\Activity;
use App\Models\Attachment;
use App\Models\JobTitle;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    /**
     * -------------------------------
     * ------------ Users ------------
     * -------------------------------
     */
    Route::get('/users', function () {
        return Inertia::render('Users/Index', [
            'users' => User::with('role', 'jobTitle')->get(),
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
            'user' => $user->load([
                'role',
                'jobTitle',
                'notes',
                'tasks',
                'activity',
                'learnings',
                'deals' => fn ($q) => $q->with([
                    'company',
                    'pipeline',
                    'stage',
                ]),
            ]),
        ]);
    })->name('users.show');

    Route::get('/users/{user}/edit', function (User $user) {
        return Inertia::render('Users/Update', [
            'user' => $user->load('role', 'jobTitle'),
            'roles' => Role::all(),
            'jobTitles' => JobTitle::all(),
        ]);
    })->name('users.edit');

    /**
     * ------------------------------------
     * ------------ Activities ------------
     * ------------------------------------
     */
    Route::get('/activities', function () {
        return Inertia::render('Activities/Index', [
            'activities' => Activity::with([
                'user',
                'subject',
            ])->latest()->get(),
        ]);
    })->name('activities.index');

    Route::get('/activities/create', function () {
        return Inertia::render('Activities/Create', [
            'subjectTypes' => array_keys(Relation::morphMap()),
        ]);
    })->name('activities.create');

    Route::get('/activities/{activity}', function (Activity $activity) {
        return Inertia::render('Activities/Show', [
            'activity' => $activity->load([
                'user',
                'subject',
                'notes',
                'tasks',
                'attachments',
            ]),
        ]);
    })->name('activities.show');

    Route::get('/activities/{activity}/edit', function (Activity $activity) {
        return Inertia::render('Activities/Update', [
            'activity' => $activity->load([
                'user',
                'subject',
            ]),
            'subjectTypes' => array_keys(Relation::morphMap()),
        ]);
    })->name('activities.edit');

    /**
     * -------------------------------------
     * ------------ Attachments ------------
     * -------------------------------------
     */
    Route::get('/attachments', function () {
        return Inertia::render('Attachments/Index', [
            'attachments' => Attachment::with([
                'uploader',
            ])->latest()->get(),
        ]);
    })->name('attachments.index');

    Route::get('/attachments/create', function () {
        return Inertia::render('Attachments/Create', [
            'subjectTypes' => array_keys(Relation::morphMap()),
        ]);
    })->name('attachments.create');

    Route::get('/attachments/{attachment}', function (Attachment $attachment) {
        return Inertia::render('Attachments/Show', [
            'attachment' => $attachment->load([
                'uploader',
            ]),
        ]);
    })->name('attachments.show');

    Route::get('/activities/{attachment}/edit', function (Attachment $attachment) {
        return Inertia::render('Attachments/Update', [
            'attachment' => $attachment->load([
                'uploader',
            ]),
            'subjectTypes' => array_keys(Relation::morphMap()),
        ]);
    })->name('attachments.edit');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
