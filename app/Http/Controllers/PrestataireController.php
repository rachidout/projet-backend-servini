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

class PrestataireController extends Controller
{

    // ================================
    // REGISTER
    // ================================
    public function register(RegisterPrestataireRequest $request)
    {
        $validatedData = $request->validated();

        // CHEMIN PUBLIC
        $imageDefault = asset('profile_image/default-profile.jpg');

        $prestataire = Prestataire::create([
            'nom' => $validatedData['nom'],
            'prenom' => $validatedData['prenom'],
            'email' => $validatedData['email'],
            'telephone' => $validatedData['telephone'],
            'ville' => $validatedData['ville'],
            'zone' => $validatedData['zone'],
            'password' => Hash::make($validatedData['password']),
            'note_moyenne' => 0,
            'image' => null, // image par défaut sera envoyée côté front
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

    // ================================
    // LOGIN
    // ================================
    public function login(LoginPrestataireRequest $request)
    {
        $prestataire = Prestataire::where('email', $request->email)->first();

        if (!$prestataire || !Hash::check($request->password, $prestataire->password)) {
            throw ValidationException::withMessages([
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

    // ================================
    // UPDATE PROFILE
    // ================================
    public function updateProfile(UpdateProfileRequest $request)
    {
        $prestataire = $request->user();
        $validatedData = $request->validated();

        $prestataire->nom = $validatedData['nom'];
        $prestataire->prenom = $validatedData['prenom'];
        $prestataire->email = $validatedData['email'];
        $prestataire->telephone = $validatedData['telephone'];

        if ($request->hasFile('image')) {

            // Delete old file (PUBLIC)
            if ($prestataire->image) {
                $oldPath = public_path('profile_image/' . $prestataire->image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            // Save new file (PUBLIC)
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('profile_image'), $imageName);

            $prestataire->image = $imageName;
        }

        $prestataire->save();

        $imageDefault = asset('profile_image/default-profile.jpg');

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'prestataire' => [
                'nom' => $prestataire->nom,
                'prenom' => $prestataire->prenom,
                'email' => $prestataire->email,
                'telephone' => $prestataire->telephone,
                'membre_depuis' => $prestataire->created_at->format('M d, Y'),
                'image' => $prestataire->image
                    ? asset('profile_image/' . $prestataire->image)
                    : $imageDefault
            ]
        ]);
    }

    // ================================
    // UPDATE PASSWORD
    // ================================
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $prestataire = Auth::user();
        $prestataire->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Mot de passe changé avec succès!'
        ], 200);
    }

    // ================================
    // GET INFO
    // ================================
    public function getInformation(Request $request)
    {
        $prestataire = $request->user();
        $imageDefault = asset('profile_image/default-profile.jpg');

        return response()->json([
            'nom' => $prestataire->nom,
            'prenom' => $prestataire->prenom,
            'email' => $prestataire->email,
            'telephone' => $prestataire->telephone,
            'membre_depuis' => $prestataire->created_at->format('M d, Y'),
            'image' => $prestataire->image
                ? asset('profile_image/' . $prestataire->image)
                : $imageDefault,
        ]);
    }

    // ================================
    // SET PARAMS
    // ================================
    public function setparameter(setparameterRequest $request)
    {
        $prestataire = Auth::user();
        $validatedData = $request->validated();

        $dataToUpdate = [
            'bio' => $validatedData['bio'] ?? $prestataire->bio,
            'prix_heure' => $validatedData['prix_heure'] ?? $prestataire->prix_heure,
            'facebook_url' => $validatedData['facebook_url'] ?? $prestataire->facebook_url,
            'linkedin_url' => $validatedData['linkedin_url'] ?? $prestataire->linkedin_url,
        ];

        // carte identité obligatoire pour pending
        if (!$request->hasFile('carte_identite') && !$prestataire->carte_identite) {
            if ($prestataire->status == 'pending') {
                return response()->json([
                    'message' => "Veuillez entrer votre pièce d’identité afin de traiter votre demande."
                ], 403);
            }
        }

        // Upload carte identité
        if ($request->hasFile('carte_identite')) {

            if ($prestataire->status == 'approved') {
                return response()->json([
                    'message' => "Vous ne pouvez pas modifier votre pièce d'identité après approbation."
                ], 403);
            }

            // delete old (PUBLIC)
            if ($prestataire->carte_identite) {
                $oldCard = public_path($prestataire->carte_identite);
                if (file_exists($oldCard)) unlink($oldCard);
            }

            // save new (PUBLIC)
            $filename = time() . '.' . $request->carte_identite->extension();
            $request->carte_identite->move(public_path('id_cards'), $filename);

            $dataToUpdate['carte_identite'] = 'id_cards/' . $filename;
        }

        $prestataire->update($dataToUpdate);

        return response()->json([
            'message' => 'Paramètres mis à jour avec succès'
        ], 200);
    }

    // ================================
    // SHOW
    // ================================
    public function show($id)
    {
        try {
            $prestataire = Prestataire::with(['reservations', 'service'])
                ->withCount(['reservations as total_prestations' => fn($q) => $q->where('statut', 'confirmee')])
                ->findOrFail($id);

            $imageDefault = asset('profile_image/default-profile.jpg');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $prestataire->id,
                    'nom' => $prestataire->nom,
                    'prenom' => $prestataire->prenom,
                    'email' => $prestataire->email,
                    'telephone' => $prestataire->telephone,
                    'ville' => $prestataire->ville,
                    'zone' => $prestataire->zone,
                    'bio' => $prestataire->bio,

                    'service_id' => $prestataire->service->id ?? null,
                    'service_name' => $prestataire->service->categorie ?? 'Non spécifié',
                    'prix' => $prestataire->prix_heure,

                    'facebook_url' => $prestataire->facebook_url,
                    'linkedin_url' => $prestataire->linkedin_url,
                    'membre_depuis' => $prestataire->created_at->format('M d, Y'),

                    'photo' => $prestataire->image
                        ? asset('profile_image/' . $prestataire->image)
                        : $imageDefault,

                    'total_prestations' => $prestataire->total_prestations ?? 0,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Prestataire non trouvé'], 404);
        }
    }

    // ================================
    // LOGOUT
    // ================================
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie!'
        ], 200);
    }

    // ================================
    // INDEX LIST
    // ================================
    public function index(Request $request)
    {
        // (inchangé — juste images corrigées)
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
                'reservations as nombre_prestations' => function ($query) {
                    $query->whereIn('statut', ['confirmé', 'terminé']);
                }
            ])
            ->withCount('avis as nombre_avis');

        if ($ville) $query->where('ville', $ville);
        if ($zone) $query->where('zone', $zone);

        if ($prix_min || $prix_max) {
            if ($prix_min && $prix_max) $query->whereBetween('prix_heure', [$prix_min, $prix_max]);
            elseif ($prix_min) $query->where('prix_heure', '>=', $prix_min);
            elseif ($prix_max) $query->where('prix_heure', '<=', $prix_max);
        }

        if ($categorie) {
            $query->whereHas('service', function ($q) use ($categorie) {
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
            default:
                $query->orderBy('created_at', 'desc');
        }

        $prestataires = $query->paginate($per_page);
        $imageDefault = asset('profile_image/default-profile.jpg');

        $prestatairesList = $prestataires->map(function ($p) use ($imageDefault) {
            return [
                'id' => $p->id,
                'nom' => $p->nom,
                'prenom' => $p->prenom,
                'image' => $p->image ? asset('profile_image/' . $p->image) : $imageDefault,
                'ville' => $p->ville,
                'zone' => $p->zone,
                'prix_heure' => $p->prix_heure,
                'note_moyenne' => round($p->note_moyenne ?? 0, 1),
                'nombre_prestations' => $p->nombre_prestations ?? 0,
                'nombre_avis' => $p->nombre_avis ?? 0,
                'service' => [
                    'categorie' => $p->service->categorie ?? 'N/A'
                ]
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
            ]
        ], 200);
    }

    // ================================
    // DELETE
    // ================================
    public function destroy(Prestataire $prestataire)
    {
        try {
            if ($prestataire->carte_identite) {
                $path = public_path($prestataire->carte_identite);
                if (file_exists($path)) unlink($path);
            }

            if ($prestataire->image) {
                $path = public_path('profile_image/' . $prestataire->image);
                if (file_exists($path)) unlink($path);
            }

            $prestataire->delete();

            return response()->json(['message' => 'Prestataire supprimé avec succès.'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la suppression.', 'error' => $e->getMessage()], 500);
        }
    }

    // ================================
    // ACTIVATE
    // ================================
    public function activate(Prestataire $prestataire)
    {
        try {
            $prestataire->status = 'active';
            $prestataire->save();

            return response()->json(['message' => 'Prestataire activé avec succès.'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de l\'activation.', 'error' => $e->getMessage()], 500);
        }
    }
}
