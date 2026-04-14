<?php

use App\Enums\UserRole;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    if (auth()->check()) {
        $role = auth()->user()->role->value;
        return ($role === UserRole::STUDENT->value) ? redirect('/student') : redirect('/admin');
    }
    return redirect('/'); // Kirim ke Landing Page untuk pilih Login
})->name('login');


Route::get('/test-auth', function() {
    return [
        'is_logged_in' => auth()->check(),
        'user_id' => auth()->id(),
        'session_id' => session()->getId(),
        'user_data' => auth()->user(),
    ];
})->middleware('web');
