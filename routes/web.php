<?php

use App\Http\Controllers\OrphanController;
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

Route::post('/orphans', [OrphanController::class, 'create']);

Route::get('/orphans', [OrphanController::class, 'fetchOrphans']);

Route::get('/orphans/export', [OrphanController::class, 'exportOrphansToExcel']);
