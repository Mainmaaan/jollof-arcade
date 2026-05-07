<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/game/{name}', function ($name) {
    return view('game', compact('name'));
});