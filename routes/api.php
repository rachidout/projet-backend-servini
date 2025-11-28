<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrestataireController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AvisController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaticsController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- ROUTE DE TEST (POUR LE DIAGNOSTIC) ---
Route::middleware('auth:sanctum')->get('/test-user', function (Request $request) {
    return response()->json([
        'message' => 'Token valide !',
        'id' => $request->user()->id,
        'type' => get_class($request->user()), // Doit afficher App\Models\Admin
    ]);
});

// ROUTES PRESTATAIRE
Route::post('/prestataire/register', [PrestataireController::class, 'register']);
Route::post('/prestataire/login', [PrestataireController::class, 'login']);
Route::middleware('auth:sanctum')->post('/prestataire/logout', [PrestataireController::class, 'logout']);

// Profil api proteger
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/prestataire/profile/update', [PrestataireController::class, 'updateProfile']);
    Route::post('/prestataire/password/update', [PrestataireController::class, 'updatePassword']);
    Route::get('/prestataire/information', [PrestataireController::class, 'getInformation']);
    Route::get('/prestataire/parameter', [PrestataireController::class, 'getparameter']);
    Route::post('/prestataire/parameter', [PrestataireController::class, 'setparameter']);
});

// Routes publiques
Route::get('/prestataires', [PrestataireController::class, 'index']);
Route::get('/prestataires/{id}', [PrestataireController::class, 'show']);


// ROUTES ADMIN
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function(){
        Route::post('/logout', [AdminController::class, 'logout']);
        Route::get('/prestataires', [AdminController::class, 'show_all']);
        Route::delete('/prestataires/{id}', [AdminController::class, 'destroy']);
        Route::put('/prestataires/activer/{id}', [AdminController::class, 'activate']);
        Route::put('/prestataires/statut/{id}', [AdminController::class, 'updateStatus']);
    });
});


// ROUTES DIVERSES


// Réservation
Route::post('/reservations', [ReservationController::class, 'store']);
Route::get('/reservations/{id}', [ReservationController::class, 'show']);
Route::get('/prestataires/{id}/reservations', [ReservationController::class, 'byPrestataire']);
Route::patch('/reservations/{id}/statut', [ReservationController::class, 'updateStatus']);

// Statistiques
Route::get('/stats', [StaticsController::class, 'index']);
Route::middleware('auth:sanctum')->get('/stats/prestataire', [StaticsController::class, 'staticsprestataire']);

// Services
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);
Route::get('/prestataire/{id}/service', [ServiceController::class, 'getServiceByPrestataire']);
Route::post('/services', [ServiceController::class, 'store']);
Route::put('/services/{id}', [ServiceController::class, 'update']);
Route::delete('/services/{id}', [ServiceController::class, 'destroy']);

// Avis
Route::get('/prestataires/{prestataire_id}/avis', [AvisController::class, 'getAvisByPrestataire']);
Route::post('/avis', [AvisController::class, 'store']);
Route::delete('/avis/{id}', [AvisController::class, 'destroy']);
