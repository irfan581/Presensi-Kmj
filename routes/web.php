<?php

use Illuminate\Support\Facades\Route;

// Biar pas buka IP langsung masuk ke gerbang Filament
Route::redirect('/', '/admin');