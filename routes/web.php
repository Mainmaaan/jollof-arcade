<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/game/{name}', function ($name) {
    return view('game', compact('name'));
});