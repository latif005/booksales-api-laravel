<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;

Route::get('/books', [BookController::class, 'index']);
Route::get('/authors', [AuthorController::class, 'index']);