
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\MidtransController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
| Route yang dapat diakses tanpa login.
*/

// Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Books
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);

// Genres
Route::get('/genres', [GenreController::class, 'index']);
Route::get('/genres/{id}', [GenreController::class, 'show']);

// Authors
Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/authors/{id}', [AuthorController::class, 'show']);

// Contact Admin
Route::post('/contact', [ContactMessageController::class, 'store']);

// Midtrans Webhook Notification
Route::post('/midtrans/notification', [MidtransController::class, 'notification']);



/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
| Route yang membutuhkan login.
*/

Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

});


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Hanya dapat diakses oleh pengguna dengan role Admin.
*/

Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    // Users
    Route::get('/users', [UserController::class, 'index']);

    // Books Management
    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::patch('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);

    // Genres Management
    Route::post('/genres', [GenreController::class, 'store']);
    Route::put('/genres/{id}', [GenreController::class, 'update']);
    Route::patch('/genres/{id}', [GenreController::class, 'update']);
    Route::delete('/genres/{id}', [GenreController::class, 'destroy']);

    // Authors Management
    Route::post('/authors', [AuthorController::class, 'store']);
    Route::put('/authors/{id}', [AuthorController::class, 'update']);
    Route::patch('/authors/{id}', [AuthorController::class, 'update']);
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);

    // Transactions Management
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

    // Contact Messages
    Route::get('/contact-messages', [ContactMessageController::class, 'index']);

});


/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
| Hanya dapat diakses oleh pengguna dengan role Customer.
*/

Route::middleware(['auth:sanctum', 'customer'])->group(function () {
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::post('/transactions/checkout', [TransactionController::class, 'checkout']);
    Route::get('/transactions/my-history', [TransactionController::class, 'myHistory']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
});
