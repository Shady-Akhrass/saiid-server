<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AidController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrphanController;
use App\Http\Controllers\RefugeeController;
use App\Http\Controllers\ShelterController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\EmploymentController;
use App\Http\Controllers\PatientController;

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

Route::post('/teachers', [TeacherController::class, 'create']);
Route::post('/increment-visitor-teachers-count', [TeacherController::class, 'incrementVisitorCount']);

Route::post('/students', [StudentController::class, 'create']);
Route::post('/increment-visitor-students-count', [StudentController::class, 'incrementVisitorCount']);

Route::post('/employments', [EmploymentController::class, 'create']);

Route::post('/shelters', [ShelterController::class, 'create']);

Route::post('/patients', [PatientController::class, 'create']);
// Route::post('/shelters/import', [ShelterController::class, 'importShelters']);
Route::post('/refugees/import', [ShelterController::class, 'importRefugees']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orphans', [OrphanController::class, 'fetchOrphans']);
    Route::get('/orphans/dashboard', [OrphanController::class, 'fetchAllOrphansForDashboard']);
    Route::get('/orphans/export', [OrphanController::class, 'exportOrphansToExcel']);

    Route::get('/aids', [AidController::class, 'fetchAids']);
    Route::get('/aids/dashboard', [AidController::class, 'fetchAllAidsForDashboard']);
    Route::get('/aids/export', [AidController::class, 'exportAidsToExcel']);

    Route::get('/students', [StudentController::class, 'fetchStudents']);
    Route::get('/students/export', [StudentController::class, 'exportStudentToExcel']);
    Route::get('/students/dashboard', [StudentController::class, 'fetchAllStudentsForDashboard']);


    Route::get('/teachers', [TeacherController::class, 'fetchTeachers']);
    Route::get('/teachers/export', [TeacherController::class, 'exportTeacherToExcel']);
    Route::get('/teachers/dashboard', [TeacherController::class, 'fetchAllTeachersForDashboard']);

    Route::get('/employments', [EmploymentController::class, 'fetchEmployments']);
    Route::get('/employments/export', [EmploymentController::class, 'exportEmploymentToExcel']);
    Route::get('/epmloyments/dashboard', [EmploymentController::class, 'fetchAllEmploymentsForDashboard']);

    Route::get('/refugees/export', [ShelterController::class, 'exportRefugeesToExcel']);

    Route::get('/patients', [PatientController::class, 'fetchPatients']);
    Route::get('/patients/export', [PatientController::class, 'exportPatientsToExcel']);

    Route::get('/shelters', [TeacherController::class, 'fetchShelters']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/register/{id}', [AuthController::class, 'update']);
    Route::get('/user/{id}', [AuthController::class, 'fetchData']);
});


Route::get('/image/{id}', [OrphanController::class, 'show']);

Route::get('/excel/{id}', [ShelterController::class, 'show']);


Route::get('/death-certificate/{id}', [OrphanController::class, 'death_certificate']);
