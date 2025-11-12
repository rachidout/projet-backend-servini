<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrestataireController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
//Prestatire------------------------------------------------------------------------------------
Route::post('/prestataire/register', [PrestataireController::class, 'register']);
Route::post('/prestataire/login',[PrestataireController::class,'login']);
Route::post('/prestataire/profile/update',[PrestataireController::class,'updateProfile'])->middleware('auth:sanctum');
Route::post('/prestataire/password/update', [PrestataireController::class, 'updatePassword'])->middleware('auth:sanctum');
Route::get('/prestataire/information',[PrestataireController::class,'getInformation'])->middleware('auth:sanctum');
