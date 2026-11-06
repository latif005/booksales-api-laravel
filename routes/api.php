<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;

// Login
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Route yang bisa diakses semua orang
Route::get('/books', [BookController::class, 'index']);

Route::get('/genres', [GenreController::class, 'index']);
Route::get('/genres/{id}', [GenreController::class, 'show']);

Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/authors/{id}', [AuthorController::class, 'show']);

// Route khusus Admin
Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // Buku
    // Route::post('/books', [BookController::class, 'store']);
    // Route::put('/books/{id}', [BookController::class, 'update']);
    // Route::patch('/books/{id}', [BookController::class, 'update']);
    // Route::delete('/books/{id}', [BookController::class, 'destroy']);

    // Genre
    // Route::post('/genres', [GenreController::class, 'store']);
    // Route::put('/genres/{id}', [GenreController::class, 'update']);
    // Route::patch('/genres/{id}', [GenreController::class, 'update']);
    // Route::delete('/genres/{id}', [GenreController::class, 'destroy']);

    // Author
    // Route::post('/authors', [AuthorController::class, 'store']);
    // Route::put('/authors/{id}', [AuthorController::class, 'update']);
    // Route::patch('/authors/{id}', [AuthorController::class, 'update']);
    // Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);

    // Transaksi: Read All dan Destroy
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
});

// Route khusus Customer
Route::middleware(['auth:sanctum', 'customer'])->group(function () {

    // Transaksi: Create, Show, dan Update
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    Route::patch('/transactions/{id}', [TransactionController::class, 'update']);
});

Route::apiResource('/books', BookController::class)->only(['store', 'update', 'destroy']);
Route::get('/books/{id}', [BookController::class, 'show']);

Route::post('/genres', [GenreController::class, 'store']);
Route::put('/genres/{id}', [GenreController::class, 'update']);
Route::patch('/genres/{id}', [GenreController::class, 'update']);
Route::delete('/genres/{id}', [GenreController::class, 'destroy']);

Route::post('/authors', [AuthorController::class, 'store']);
Route::put('/authors/{id}', [AuthorController::class, 'update']);
Route::patch('/authors/{id}', [AuthorController::class, 'update']);
Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);
