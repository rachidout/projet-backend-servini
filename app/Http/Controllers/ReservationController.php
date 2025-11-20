<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Service;
use App\Models\Prestataire;
use App\Http\Requests\AddReservationRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationController extends Controller
{
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

            $reservation->load([
                'service' => function ($query) {
                    $query->with('prestataire');
                }
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Réservation créée avec succès',
                'data'    => $reservation
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Échec de la création de la réservation',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function byPrestataire($id)
    {
        $reservations = Reservation::where('id_prestataire', $id)->get();

        return response()->json($reservations);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|in:en_attente,confirmee,annulee',
        ]);

        $reservation = Reservation::findOrFail($id);
        $reservation->statut = $request->input('statut');
        $reservation->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour',
            'data'    => $reservation,
        ]);
    }

    public function show($id)
    {
        $reservation = Reservation::findOrFail($id);

        $service = Service::find($reservation->id_service);

        $prestataire = null;
        if ($service) {
            $prestataire = Prestataire::find($service->id_prestataire);
        }

        return response()->json([
            'id'            => $reservation->id,
            'date'          => Carbon::parse($reservation->date)->format('Y-m-d'),
            'heure'         => Carbon::parse($reservation->heure)->format('H:i'),
            'statut'        => $reservation->statut,
            'client_nom'    => $reservation->client_nom,
            'client_prenom' => $reservation->client_prenom,

            'prestataire' => [
                'nom'        => $prestataire?->nom,
                'prenom'     => $prestataire?->prenom,
                'prix_heure' => $prestataire?->prix_heure,
            ],

            'service' => [
                'categorie'  => $service?->categorie,
            ],
        ]);
    }
}
