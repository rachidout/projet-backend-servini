<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrestataireController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AvisController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaticsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ===========================================================================
// ROUTES PRESTATAIRE
// ===========================================================================
Route::post('/prestataire/register', [PrestataireController::class, 'register']);
Route::post('/prestataire/login', [PrestataireController::class, 'login']);
Route::post('/prestataire/logout', [PrestataireController::class, 'logout'])->middleware('auth:sanctum');

// Profil et paramètres (protégés)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/prestataire/profile/update', [PrestataireController::class, 'updateProfile']);
    Route::post('/prestataire/password/update', [PrestataireController::class, 'updatePassword']);
    Route::get('/prestataire/information', [PrestataireController::class, 'getInformation']);
    Route::get('/prestataire/parameter', [PrestataireController::class, 'getparameter']);
    Route::post('/prestataire/parameter', [PrestataireController::class, 'setparameter']);
});

// Routes publiques - Filtrage et recherche de prestataires
Route::get('/prestataires', [PrestataireController::class, 'index']);
Route::get('/prestataires/{id}', [PrestataireController::class, 'show']);

// ===========================================================================
// ROUTES ADMIN (avec préfixe /admin)
// ===========================================================================
Route::prefix('admin')->group(function () {
    Route::get('/prestataires', [AdminController::class, 'show_all']);
    Route::delete('/prestataires/{id}', [AdminController::class, 'destroy']);
    Route::put('/prestataires/activer/{id}', [AdminController::class, 'activate']);

    // ✅ bonne route pour changer le statut
    Route::put('/prestataires/statut/{id}', [AdminController::class, 'updateStatus']);
});

Route::get('/reservations/{id}', [ReservationController::class, 'show']);

    
// ===========================================================================
// ROUTES STATISTIQUES
// ===========================================================================
Route::get('/stats', [StaticsController::class, 'index']);
Route::get('/stats/prestataire', [StaticsController::class, 'staticsprestataire'])->middleware('auth:sanctum');

// ===========================================================================
// ROUTES RESERVATIONS
// ===========================================================================
Route::post('/reservations', [ReservationController::class, 'store']);
Route::get('/prestataires/{id}/reservations', [ReservationController::class, 'byPrestataire']);
Route::patch('/reservations/{id}/statut', [ReservationController::class, 'updateStatus']);

// ===========================================================================
// ROUTES SERVICES
// ===========================================================================
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{id}', [ServiceController::class, 'show']);
Route::get('/prestataire/{id}/service', [ServiceController::class, 'getServiceByPrestataire']);
Route::post('/services', [ServiceController::class, 'store']);
Route::put('/services/{id}', [ServiceController::class, 'update']);
Route::delete('/services/{id}', [ServiceController::class, 'destroy']);



// Routes Avis
//Route::get('/prestataires/{id}/avis', [AvisController::class, 'getAvisByPrestataire']);
//Route::post('/avis', [AvisController::class, 'store']);
//Route::delete('/avis/{id}', [AvisController::class, 'destroy']);

Route::get('/prestataires/{prestataire_id}/avis', [AvisController::class, 'getAvisByPrestataire']);
Route::post('/avis', [AvisController::class, 'store']);
Route::delete('/avis/{id}', [AvisController::class, 'destroy']);