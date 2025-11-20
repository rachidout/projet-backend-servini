<?php

namespace App\Http\Controllers;

use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

       public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);


        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return response()->json(['message' => 'Aucun compte admin trouvé avec cet email'], 401);
        }

        if (!Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Mot de passe incorrect'], 401);
        }

        $admin->tokens()->delete();


        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ],
            'role' => 'admin'
        ], 200);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie'], 200);
    }

    //bach nrj3o lih tous les prestataires
    //
    public function show_all()
    {
        try {
            $prestataires = Prestataire::all();

            $prestataires = $prestataires->map(function ($prestataire) {
                return [
                    'id'             => $prestataire->id,
                    'nom'            => $prestataire->nom,
                    'prenom'         => $prestataire->prenom,
                    'telephone'      => $prestataire->telephone,
                    'email'          => $prestataire->email,
                    'ville'          => $prestataire->ville,
                    'status'         => $prestataire->statut ?? 'pending',
                    'carte_identite' => $prestataire->carte_identite,
                ];
            });

            return response()->json($prestataires, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des prestataires.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    // Supprimer un prestataire

    public function destroy($id)
    {
        try {
            $prestataire = Prestataire::findOrFail($id);

            if ($prestataire->carte_identite) {
                Storage::disk('public')->delete('id_cards/' . $prestataire->carte_identite);
            }

            $prestataire->delete();

            return response()->json([
                'message' => 'Prestataire supprimé avec succès.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function activate($id)
    {
        try {
            $prestataire = Prestataire::findOrFail($id);

            $prestataire->statut = 'active';
            $prestataire->save();

            return response()->json([
                'message'     => 'Prestataire activé avec succès.',
                'prestataire' => [
                    'id'     => $prestataire->id,
                    'status' => $prestataire->statut,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'activation.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    // Changer le statut (pending / active / rejected)

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,active,rejected',
        ]);

        try {
            $prestataire = Prestataire::findOrFail($id);
            $prestataire->statut = $request->input('status');
            $prestataire->save();

            return response()->json([
                'message'     => 'Statut mis à jour avec succès.',
                'prestataire' => [
                    'id'     => $prestataire->id,
                    'status' => $prestataire->statut,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du statut.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
