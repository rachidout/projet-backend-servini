<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrestataireController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaticsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
//Prestatire------------------------------------------------------------------------------------
Route::post('/prestataire/register', [PrestataireController::class, 'register']);
Route::post('/prestataire/login',[PrestataireController::class,'login']);
Route::post('/prestataire/profile/update',[PrestataireController::class,'updateProfile'])->middleware('auth:sanctum');
Route::post('/prestataire/password/update', [PrestataireController::class, 'updatePassword'])->middleware('auth:sanctum');
Route::get('/prestataire/information',[PrestataireController::class,'getInformation'])->middleware('auth:sanctum');
Route::get('/prestataire/parameter', [PrestataireController::class, 'getparameter'])->middleware('auth:sanctum');
Route::post('/prestataire/parameter',[PrestataireController::class,'setparameter'])->middleware('auth:sanctum');

// -----hadi dyal filtrage
Route::get('/prestataires', [PrestataireController::class, 'index']);

// -----hadi dyal admin
//Route::get('prestataires', [PrestataireController::class, 'show_all']);
Route::delete('prestataires/{prestataire}', [PrestataireController::class, 'destroy']);
Route::put('prestataires/activer/{prestataire}', [PrestataireController::class, 'activate']);


Route::get('/prestataires/{id}', [PrestataireController::class, 'show']);


Route::post('/prestataire/logout',[PrestataireController::class,'logout'])->middleware('auth:sanctum');



Route::get('/stats', [StaticsController::class, 'index']);
Route::get('/stats/prestataire', [StaticsController::class, 'staticsprestataire'])->middleware('auth:sanctum');

Route::post('/reservations', [ReservationController::class, 'store']);
Route::get('/prestataires/{id}/reservations', [ReservationController::class, 'byPrestataire']);
Route::patch('/reservations/{id}/statut', [ReservationController::class, 'updateStatus']);


//Services------------------------------------------------------------------------------------
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);
Route::get('/prestataire/{id}/service', [ServiceController::class, 'getServiceByPrestataire']);
Route::post('/services', [ServiceController::class, 'store']);
Route::put('/services/{id}', [ServiceController::class, 'update']);
Route::delete('/services/{id}', [ServiceController::class, 'destroy']);



