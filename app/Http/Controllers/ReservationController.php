<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Requests\AddReservationRequest;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Créer une nouvelle réservation
     */
    public function store(AddReservationRequest $request)
    {
        try {
            $validatedData = $request->validated();

            // Récupération du service avec son prestataire
            $service = Service::with('prestataire')->findOrFail($validatedData['service_id']);

            // Vérifier que le service a bien un prestataire
            if (!$service->prestataire) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce service n\'a pas de prestataire associé'
                ], 400);
            }

            // Vérifier que le service est actif (si la colonne existe)
            if (isset($service->statut) && $service->statut !== 'actif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce service n\'est plus disponible'
                ], 400);
            }

            // ID du prestataire associé au service
            $prestataireId = $service->prestataire->id;

            DB::beginTransaction();

            // Création de la réservation
            $reservation = Reservation::create([
                'id_prestataire'        => $prestataireId,
                'id_service'            => $validatedData['service_id'],
                'client_nom'            => $validatedData['nom'],
                'client_prenom'         => $validatedData['prenom'],
                'client_email'          => $validatedData['email'],
                'client_telephone'      => $validatedData['telephone'],
                'client_adresse'        => $validatedData['adresse'],
                'description_du_besoin' => $validatedData['description_besoin'],
                'date'                  => $validatedData['date'],
                'heure'                 => $validatedData['heure'],
                'statut'                => 'en_attente',
            ]);

            // Charger les relations pour la réponse
            $reservation->load([
                'service' => function ($query) {
                    $query->with('prestataire');
                }
            ]);

            \Log::info('Réservation créée avec relations:', [
                'reservation_id' => $reservation->id,
                'service'        => $reservation->service ? 'OK' : 'NULL',
                'prestataire'    => $reservation->service?->prestataire ? 'OK' : 'NULL'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Réservation créée avec succès',
                'data'    => $reservation
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Erreur réservation:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Échec de la création de la réservation',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les réservations d'un prestataire donné (appelé depuis React)
     * GET /api/prestataires/{id}/reservations
     */
    public function byPrestataire($id)
    {
        // Log pour debug
        \Log::info('Liste des réservations pour le prestataire', [
            'id_prestataire' => $id,
        ]);

        // Récupérer toutes les réservations où id_prestataire = {id}
        $reservations = Reservation::where('id_prestataire', $id)->get();

        return response()->json($reservations);
    }

    public function updateStatus(Request $request, $id)
{
    // valider le statut reçu
    $request->validate([
        'statut' => 'required|in:en_attente,confirmee,annulee',
    ]);

    // récupérer la réservation
    $reservation = Reservation::findOrFail($id);

    // mettre à jour le statut
    $reservation->statut = $request->input('statut');
    $reservation->save();

    return response()->json([
        'success' => true,
        'message' => 'Statut mis à jour',
        'data'    => $reservation,
    ]);
}

}
