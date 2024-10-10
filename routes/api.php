<?php

use App\Http\Controllers\AidController;
use App\Http\Controllers\OrphanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::post('/aids', [AidController::class, 'create']);
Route::post('/increment-visitor-aids-count', [AidController::class, 'incrementVisitorCount']);

Route::post('/orphans', [OrphanController::class, 'create']);
Route::post('/increment-visitor-orphans-count', [OrphanController::class, 'incrementVisitorCount']);

Route::post('/teacher', [TeacherController::class, 'create']);

Route::post('/student', [StudentController::class, 'create']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orphans', [OrphanController::class, 'fetchOrphans']);
    Route::get('/aids', [AidController::class, 'fetchAids']);

    Route::get('/orphans/dashboard', [OrphanController::class, 'fetchAllOrphansForDashboard']);

    Route::get('/orphans/export', [OrphanController::class, 'exportOrphansToExcel']);
    Route::get('/aids/export', [AidController::class, 'exportAidsToExcel']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/register/{id}', [AuthController::class, 'update']);
    Route::get('/user/{id}', [AuthController::class, 'fetchData']);
});


Route::get('/image/{id}', [OrphanController::class, 'show']);

Route::get('/death-certificate/{id}', [OrphanController::class, 'death_certificate']);
