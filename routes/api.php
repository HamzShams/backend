<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\MarkController;

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

// For Test
Route::get('/test-online', function () {
    return 1;
});

// Auth
Route::post('/add-admin',[AuthController::class,'addAdmin']);

Route::post('/register',[AuthController::class,'Register']);
Route::post('/login',[AuthController::class,'Login']);

Route::group(['middleware' => ['auth:api']], function(){

    // Auth
    Route::post('/add-employee',[AuthController::class,'addEmployee']);
    Route::post('/logout',[AuthController::class,'Logout']);

    // User
    Route::get('/user',[UserController::class,'Me']);
    Route::put('/user',[userController::class,'Update']);
    Route::get('/users',[UserController::class,'getEmployees']);
    Route::post('/users',[UserController::class,'searchEmployees']);
    Route::put('/user/{id}',[userController::class,'updateEmployee']);
    Route::delete('/user/{id}',[userController::class,'deleteEmployee']);

    // Event
    Route::post('/event',[EventController::class,'addEvent']);
    Route::get('/events',[EventController::class,'getEvents']);
    Route::post('/events',[EventController::class,'searchEvents']);
    Route::put('/event/{id}',[EventController::class,'updateEvent']);
    Route::delete('/event/{id}',[EventController::class,'deleteEvent']);

    // Shipment
    Route::post('/shipment',[ShipmentController::class,'addShipment']);
    Route::get('/student-shipments',[ShipmentController::class,'getStudentShipments']);
    Route::get('/shipments',[ShipmentController::class,'getShipments']);
    Route::post('/shipments',[ShipmentController::class,'searchShipments']);
    Route::post('/accept-shipment/{id}',[ShipmentController::class,'acceptShipment']);
    Route::post('/cancel-shipment/{id}',[ShipmentController::class,'cancelShipment']);

    // Course
    Route::post('/course',[CourseController::class,'addCourse']);
    Route::get('/courses',[CourseController::class,'getCourses']);
    Route::get('/course/{id}',[CourseController::class,'getCourse']);
    Route::post('/courses',[CourseController::class,'searchCourses']);
    Route::delete('/course/{id}',[CourseController::class,'deleteCourse']);
    Route::post('/start-course/{id}',[CourseController::class,'startCourse']);
    Route::post('/finish-course/{id}',[CourseController::class,'finishCourse']);
    Route::get('/available-courses',[CourseController::class,'getAvailableCourses']);
    Route::post('/available-courses',[CourseController::class,'searchAvailableCourses']);
    Route::get('/course-students/{id}',[CourseController::class,'getCourseStudents']);

    // Reservation
    Route::post('/reservation/{course_id}',[ReservationController::class,'addReservation']);
    Route::get('/student-reservations',[ReservationController::class,'getStudentReservations']);
    Route::post('/student-reservations',[ReservationController::class,'searchStudentReservations']);
    Route::get('/reservations',[ReservationController::class,'getReservations']);
    Route::post('/reservations',[ReservationController::class,'searchReservations']);

    // Mark
    Route::get('/marks/{course_id}',[MarkController::class,'getCourseMarks']);
    Route::get('/mark/{course_id}',[MarkController::class,'getStudentMark']);
    Route::post('/marks',[MarkController::class,'addCourseMarks']);
});