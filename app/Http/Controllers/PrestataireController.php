<?php

namespace App\Http\Controllers;

use App\Models\Prestataire;
use App\Models\Service;
use App\Http\Requests\RegisterPrestataireRequest;
use App\Http\Requests\LoginPrestataireRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PrestataireController extends Controller
{
  public function register(RegisterPrestataireRequest $request){
       $validatedData = $request->validated();
      $prestataire = Prestataire::create([
            'nom' => $validatedData['nom'],
            'prenom' => $validatedData['prenom'],
            'email' => $validatedData['email'],
            'telephone' => $validatedData['telephone'],
            'ville' => $validatedData['ville'],
            'zone' => $validatedData['zone'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $service = Service::create([
         'id_prestataire' => $prestataire->id,
         'categorie' => $validatedData['categorie'],
   ]);
    return response()->json([
        'message' => 'Prestataire inscrit avec succes!',
        'prestataire' => $prestataire,
         'service' => $service
    ], 201);
}
 public function login(LoginPrestataireRequest $request) {
       $prestataire = Prestataire::where('email',$request->email)->first();
   if( !$prestataire || !Hash::check($request->password, $prestataire->password)){
       throw ValidationException::withMessages(
        [
            'email' => ['Les informations de connexion sont incorrectes!'],
        ]);
   }
   $token = $prestataire->createToken('auth_token')->plainTextToken;
   return response()->json([
    'message' => 'Connexion reussie!',
    'prestataire' => $prestataire,
    'token' => $token,
    'token_type' => 'Bearer',
   ], 200);
  }
}
