<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Models\Reservation;
use App\Models\Prestataire;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    /**
     * Récupérer tous les avis d'un prestataire
     */
    public function getAvisByPrestataire($prestataire_id)
{
    try {
        // Récupérer les avis liés au prestataire via les réservations
        $avis = Avis::with('reservation')
            ->whereHas('reservation', function ($query) use ($prestataire_id) {
                $query->where('id_prestataire', $prestataire_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Formater les avis
        $avisFormatted = $avis->map(function ($item) {
            return [
                'id'            => $item->id,
                'note'          => (int) $item->note,
                'commentaire'   => $item->commentaire,
                'date'          => $item->created_at->format('d/m/Y'),
                'client_nom'    => $item->reservation->client_nom ?? 'Client',
                'client_prenom' => $item->reservation->client_prenom ?? 'Anonyme',
            ];
        });

        // Calculer la note moyenne
        $noteMoyenne = $avis->avg('note') ?? 0;

        return response()->json([
            'avis'          => $avisFormatted,
            'note_moyenne'  => round($noteMoyenne, 1),  // exemple : 4.3
            'nombre_avis'   => $avis->count(),
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Erreur getAvisByPrestataire: ' . $e->getMessage());

        return response()->json([
            'message' => 'Erreur lors de la récupération des avis.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    
    


    /**
     * Ajouter un avis pour une réservation
     */
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'id_reservation' => 'required|exists:reservations,id',
            'note'           => 'required|integer|min:1|max:5',
            'commentaire'    => 'required|string|max:1000',
        ]);

        // Vérifier la réservation
        $reservation = Reservation::findOrFail($validated['id_reservation']);
        
        if ($reservation->statut !== 'confirmee') {
            return response()->json([
                'message' => 'Vous ne pouvez laisser un avis que pour une réservation confirmée.'
            ], 403);
        }

        // Vérifier qu'un avis n'existe pas déjà
        if (Avis::where('id_reservation', $validated['id_reservation'])->exists()) {
            return response()->json([
                'message' => 'Un avis existe déjà pour cette réservation.'
            ], 409);
        }

        // 📌 Création de l'avis
        $avis = Avis::create($validated);

        // 📌 Récupérer le prestataire lié à la réservation
        $prestataire_id = $reservation->id_prestataire;

        // 📌 Recalculer la nouvelle moyenne
        $noteMoyenne = Avis::whereHas('reservation', function ($query) use ($prestataire_id) {
                $query->where('id_prestataire', $prestataire_id);
            })
            ->avg('note');

        $noteMoyenne = round($noteMoyenne, 1);

        // 📌 Mettre à jour la colonne note_moyenne dans la table prestataires
        Prestataire::where('id', $prestataire_id)
            ->update(['note_moyenne' => $noteMoyenne]);

        return response()->json([
            'message'        => 'Avis ajouté avec succès.',
            'avis'           => $avis,
            'note_moyenne'   => $noteMoyenne
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de l\'ajout de l\'avis.',
            'error'   => $e->getMessage()
        ], 500);
    }
}


    /**
     * Supprimer un avis
     */
    public function destroy($id)
{
    try {
        $avis = Avis::with('reservation')->findOrFail($id);
        
        // Récupérer l'ID du prestataire avant de supprimer
        $prestataire_id = $avis->reservation->id_prestataire;
        
        // Supprimer l'avis
        $avis->delete();

        // Recalculer la note moyenne
        $noteMoyenne = Avis::whereHas('reservation', function ($query) use ($prestataire_id) {
                $query->where('id_prestataire', $prestataire_id);
            })
            ->avg('note');

        // Mettre à jour le prestataire
        if ($noteMoyenne) {
            Prestataire::where('id', $prestataire_id)
                ->update(['note_moyenne' => round($noteMoyenne, 1)]);
        } else {
            // Si plus d'avis, remettre à 0
            Prestataire::where('id', $prestataire_id)
                ->update(['note_moyenne' => 0]);
        }

        return response()->json([
            'message' => 'Avis supprimé avec succès.',
            'note_moyenne' => $noteMoyenne ? round($noteMoyenne, 1) : 0
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de la suppression de l\'avis.',
            'error' => $e->getMessage()
        ], 500);
    }
}
}