<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Requests\AddReservationRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class ReservationController extends Controller
{
    /**
     * Créer une nouvelle réservation
     */

public function store(AddReservationRequest $request)
{
    try {
        $validatedData = $request->validated();

        $service = Service::with('prestataire')->findOrFail($validatedData['service_id']);

        if (!$service->prestataire) {
            return response()->json([
                'success' => false,
                'message' => 'Ce service n\'a pas de prestataire associé'
            ], 400);
        }

        if (isset($service->statut) && $service->statut !== 'actif') {
            return response()->json([
                'success' => false,
                'message' => 'Ce service n\'est plus disponible'
            ], 400);
        }

        $prestataireId = $service->prestataire->id;

        DB::beginTransaction();

        $reservation = Reservation::create([
            'id_prestataire' => $prestataireId,
            'id_service' => $validatedData['service_id'],
            'client_nom' => $validatedData['nom'],
            'client_prenom' => $validatedData['prenom'],
            'client_email' => $validatedData['email'],
            'client_telephone' => $validatedData['telephone'],
            'client_adresse' => $validatedData['adresse'],
            'description_du_besoin' => $validatedData['description_besoin'],
            'date' => $validatedData['date'],
            'heure' => $validatedData['heure'],
            'statut' => 'en_attente'
        ]);

        $reservation->load([
            'service' => function($query) {
                $query->with('prestataire');
            }
        ]);

        \Log::info('Réservation créée avec relations:', [
            'reservation_id' => $reservation->id,
            'service' => $reservation->service ? 'OK' : 'NULL',
            'prestataire' => $reservation->service?->prestataire ? 'OK' : 'NULL'
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Réservation créée avec succès',
            'data' => $reservation
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
            'error' => $e->getMessage()
        ], 500);
    }
}
}
