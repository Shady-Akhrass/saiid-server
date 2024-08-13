<?php

use App\Http\Controllers\OrphanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/orphans', [OrphanController::class, 'create']);

Route::get('/orphans', [OrphanController::class, 'fetchOrphans']);

Route::get('/orphans/export', [OrphanController::class, 'exportOrphansToExcel']);
