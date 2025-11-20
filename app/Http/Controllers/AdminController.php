<?php

namespace App\Http\Controllers;

use App\Models\Prestataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // =========================
    // Récupérer tous les prestataires
    // =========================
    public function show_all()
    {
        try {
            $prestataires = Prestataire::all();

            // Adapter les champs pour le frontend
            $prestataires = $prestataires->map(function ($prestataire) {
                return [
                    'id'             => $prestataire->id,
                    'nom'            => $prestataire->nom,
                    'prenom'         => $prestataire->prenom,
                    'telephone'      => $prestataire->telephone,
                    'email'          => $prestataire->email,
                    'ville'          => $prestataire->ville,
                    // DB = "statut", Front = "status"
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

    // =========================
    // Supprimer un prestataire
    // =========================
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

    // =========================
    // Activer (ancienne route, optionnelle)
    // =========================
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

    // =========================
    // Changer le statut (pending / active / rejected)
    // =========================
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
