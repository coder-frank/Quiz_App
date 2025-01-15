<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {
    Route::post('/add-department', [AdminController::class, 'addDepartment']);
    Route::post('/add-question', [AdminController::class, 'addQuestion']);
    Route::get('/', [AdminController::class, 'index']);
});

Route::prefix('quiz')->group(function () {
    Route::get('/question/{id}', [QuizController::class, 'getQuestion']);
    Route::post('/submit-answer', [QuizController::class, 'submitAnswer']);
    Route::get('/', [QuizController::class, 'index']);
});