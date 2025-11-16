<?php

namespace App\Http\Controllers;

use App\Models\Prestataire;
use App\Models\Service;
use App\Http\Requests\RegisterPrestataireRequest;
use App\Http\Requests\LoginPrestataireRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\setparameterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class PrestataireController extends Controller
{
  public function register(RegisterPrestataireRequest $request)
{
    $validatedData = $request->validated();

    $imageDefault = asset('storage/profile_image/default-profile.jpg');

    $prestataire = Prestataire::create([
        'nom' => $validatedData['nom'],
        'prenom' => $validatedData['prenom'],
        'email' => $validatedData['email'],
        'telephone' => $validatedData['telephone'],
        'ville' => $validatedData['ville'],
        'zone' => $validatedData['zone'],
        'password' => Hash::make($validatedData['password']),
        'note_moyenne' => 0,
        'image' => $imageDefault,
    ]);

    $service = Service::create([
        'id_prestataire' => $prestataire->id,
        'categorie' => $validatedData['categorie'],
    ]);

    return response()->json([
        'message' => 'Prestataire inscrit avec succès!',
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

public function getInformation(Request $request){
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

public function setparameter(setparameterRequest $request){
    $prestataire = Auth::user();
    $validatedData = $request->validated();
    $dataToUpdate = [
        'bio' => $validatedData['bio'] ?? $prestataire->bio,
        'prix' => $validatedData['prix_heure'] ?? $prestataire->prix_heure,
        'facebook_url' => $validatedData['facebook_url'] ?? $prestataire->facebook_url ,
        'linkedin_url' => $validatedData['linkedin_url'] ?? $prestataire->linkedin_url ,
    ];
      if(!$request->hasFile('carte_identite') && !$prestataire->carte_identite){
        if($prestataire->status == 'pending'){
            return response()->json([
                'message' => "Veuillez entrer votre pièce d’identité afin de traiter votre demande.  "
            ], 403);
        }
      }

  if($request->hasFile('carte_identite')){
    //mli kaykon staus approved
     if($prestataire->status == 'approved'){
       return response()->json([
            'message' => "Vous ne pouvez pas modifier votre piece d'identite apres approbation.",
         ], 403);
     }
     //mli kaykon status pending wla rejected
      if($prestataire->carte_identite){
        Storage::disk('public')->delete($prestataire->carte_identite);
      }
      $path = $request->file('carte_identite')->store('id_cards' , 'public');
      $dataToUpdate['carte_identite'] = $path;
  }
  $prestataire->update($dataToUpdate);
    return response()->json([
        'message' => 'Parametres mis a jour avec succes '
    ], 200);
}

public function getparameter(Request $request){
    $prestataire = Auth::user();

    return response()->json([
       'status' => $prestataire->status,
        'bio' => $prestataire->bio,
        'carte_identite_path' => $prestataire->carte_identite,
        'prix_heure' => $prestataire->prix_heure,
        'facebook_url' => $prestataire->facebook_url,
        'linkedin_url' => $prestataire->linkedin_url,
    ], 200);
}

public function logout(Request $request){
    $prestataire = $request->user();
    $prestataire->tokens()->delete();
    return response()->json([
        'message' => 'Déconnexion réussie!'
    ], 200);


}


  public function index(Request $request)
    {
        $ville = $request->query('ville');
        $zone = $request->query('zone');
        $prix_min = $request->query('prix_min');
        $prix_max = $request->query('prix_max');
        $categorie = $request->query('categorie');
        $note_min = $request->query('note_min');
        $sort = $request->query('sort', 'created_at');
        $per_page = $request->query('per_page', 12);

        $query = Prestataire::query()
            ->with(['service'])
            ->withCount([
                'reservations as nombre_prestations' => function($query) {
                    $query->whereIn('statut', ['confirmé', 'terminé']);
                }
            ])
            ->withCount('avis as nombre_avis');

        if ($ville) {
            $query->where('ville', $ville);
        }

        if ($zone) {
            $query->where('zone', $zone);
        }

        if ($prix_min || $prix_max) {
            if ($prix_min && $prix_max) {
                $query->whereBetween('prix_heure', [$prix_min, $prix_max]);
            } elseif ($prix_min) {
                $query->where('prix_heure', '>=', $prix_min);
            } elseif ($prix_max) {
                $query->where('prix_heure', '<=', $prix_max);
            }
        }

        if ($categorie) {
            $query->whereHas('service', function($q) use ($categorie) {
                $q->where('categorie', $categorie);
            });
        }

        if ($note_min) {
            $query->where('note_moyenne', '>=', $note_min);
        }

        switch ($sort) {
            case 'note_moyenne':
                $query->orderBy('note_moyenne', 'desc');
                break;
            case 'prix_heure':
                $query->orderBy('prix_heure', 'asc');
                break;
            case 'nombre_prestations':
                $query->orderBy('nombre_prestations', 'desc');
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }


        $prestataires = $query->paginate($per_page);

        $imageDefault = asset('storage/profile_image/default-profile.jpg');

        $prestatairesList = $prestataires->map(function($prestataire) use ($imageDefault) {
            return [
                'id' => $prestataire->id,
                'nom' => $prestataire->nom,
                'prenom' => $prestataire->prenom,
                'image' => $prestataire->image
                    ? asset('storage/profile_image/' . $prestataire->image)
                    : $imageDefault,
                'ville' => $prestataire->ville,
                'zone' => $prestataire->zone,
                'prix_heure' => $prestataire->prix_heure,
                'note_moyenne' => round($prestataire->note_moyenne ?? 0, 1),
                'nombre_prestations' => $prestataire->nombre_prestations ?? 0,
                'nombre_avis' => $prestataire->nombre_avis ?? 0,
                'service' => [
                    'categorie' => $prestataire->service->categorie ?? 'N/A'
                ]
            ];
        });

        $villes = Prestataire::distinct()->pluck('ville')->filter()->values();
        $zones = Prestataire::distinct()->pluck('zone')->filter()->values();

        $categories = Service::select('categorie')
            ->selectRaw('COUNT(DISTINCT id_prestataire) as count')
            ->whereNotNull('id_prestataire')
            ->groupBy('categorie')
            ->get()
            ->map(function($service) {
                return [
                    'nom' => $service->categorie,
                    'count' => $service->count
                ];
            });

        return response()->json([
            'prestataires' => $prestatairesList,
            'pagination' => [
                'current_page' => $prestataires->currentPage(),
                'last_page' => $prestataires->lastPage(),
                'total' => $prestataires->total(),
                'per_page' => $prestataires->perPage(),
                'from' => $prestataires->firstItem(),
                'to' => $prestataires->lastItem()
            ],
            'filters' => [
                'villes' => $villes,
                'zones' => $zones,
                'categories' => $categories
            ]
        ], 200);
    }
}
