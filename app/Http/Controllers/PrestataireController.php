<?php

namespace App\Http\Controllers;

use App\Models\Prestataire;
use App\Models\Service;
use App\Http\Requests\RegisterPrestataireRequest;
use App\Http\Requests\LoginPrestataireRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

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
public function updateProfile(UpdateProfileRequest $request) {
    $prestataire = $request->user();

    $validatedData = $request->validated();

    $prestataire->nom = $validatedData['nom'];
    $prestataire->prenom = $validatedData['prenom'];
    $prestataire->email = $validatedData['email'];
    $prestataire->telephone = $validatedData['telephone'];

    if ($request->hasFile('image')) {
        if ($prestataire->image) {
            $oldImagePath = storage_path('app/public/profile_image/' . $prestataire->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        $imageName = time() . '.' . $request->image->extension();

        $path = $request->image->storeAs('profile_image', $imageName, 'public');

       
        $prestataire->image = $imageName;
  }

    $prestataire->save();

    $imageDefault = asset('storage/profile_image/default-profile.jpg');

    return response()->json([
        'message' => 'Profil mis à jour avec succès',
        'prestataire' => [
            'nom' => $prestataire->nom,
            'prenom' => $prestataire->prenom,
            'email' => $prestataire->email,
            'telephone' => $prestataire->telephone,
            'membre_depuis' => $prestataire->created_at->format('M d, Y'),
            'image' => $prestataire->image
                ? asset('storage/profile_image/' . $prestataire->image)
                : $imageDefault
        ]
    ]);
    }
public function updatePassword(UpdatePasswordRequest $request) {
    $prestataire = Auth::user();
    $prestataire->update(
        [
            'password' => Hash::make($request->new_password) // rani hachito 7ta hnaya
        ]
    );
    return response()->json([
        'message' => 'Mot de passe change avec succes!'
    ], 200);
}
public function getInformation(Request $request)
{
    $prestataire = $request->user();
    $imageDefault = asset('storage/profile_image/default-profile.jpg');

    return response()->json([
        'nom' => $prestataire->nom,
        'prenom' => $prestataire->prenom,
        'email' => $prestataire->email,
        'telephone' => $prestataire->telephone,
        'membre_depuis' => $prestataire->created_at->format('M d, Y'),
        'image' => $prestataire->image
            ? asset('storage/profile_image/' . $prestataire->image)
            : $imageDefault,
    ]);
}

}
