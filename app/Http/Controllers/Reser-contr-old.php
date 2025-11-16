<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Prestataire;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Requests\AddReservationRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReservationController extends Controller
{
    public function store(AddReservationRequest $request){
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'date' => 'required|date|after_or_equal:today',
            'heure' => 'required',
            'adresse' => 'required|string|max:500',
            'description_besoin' => 'required|string',
            'service_id' => 'required|exists:services,id',
            'service' => 'required|exists:services,categorie'

        ], [
            'nom.required' => 'Le nom est obligatoire',
            'prenom.required' => 'Le prénom est obligatoire',
            'telephone.required' => 'Le numéro de téléphone est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être valide',
            'date.required' => 'La date est obligatoire',
            'date.after_or_equal' => 'La date ne peut pas être dans le passé',
            'heure.required' => 'L\'heure est obligatoire',
            'adresse.required' => 'L\'adresse est obligatoire',
            'description_besoin.required' => 'La description du besoin est obligatoire',
            'service_id.required' => 'Le service est obligatoire',
            'service_id.exists' => 'Le service sélectionné n\'existe pas'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            $service = Service::with('prestataire')->findOrFail($request->service_id);
            $existingReservation = Reservation::whereHas('service', function($query) use ($service) {
                                              $query->where('prestataire_id', $service->prestataire_id);
                                          })
                                          ->where('date', $request->date)
                                          ->where('heure', $request->heure)
                                          ->where('statut', '!=', 'annulee')
                                          ->first();

            if ($existingReservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce créneau horaire n\'est pas disponible pour ce prestataire.'
                ], 409);
            }

            DB::beginTransaction();

            $reservation = Reservation::create([
                'id_prestataire' => $service->prestataire_id,
                'service_id' => $request->service_id,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
                'description_besoin' => $request->description_besoin,
                'date' => $request->date,
                'heure' => $request->heure,
                'statut' => 'en_attente'
            ]);

            $service->prestataire->increment('nb_prestations');

            DB::commit();

            $reservation->load('service.prestataire');

            return response()->json([
                'success' => true,
                'message' => 'Réservation créée avec succès',
                'data' => $reservation
            ], 201);

        } catch (ModelNotFoundException $e) {
             DB::rollBack();
             return response()->json([
                'success' => false,
                'message' => 'Service ou prestataire introuvable.',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur de création de réservation:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la réservation. Contactez l\'administrateur.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function index(Request $request){
        try {
            $query = Reservation::with('service.prestataire');

            if ($request->has('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->has('service_id')) {
                $query->where('service_id', $request->service_id);
            }

            if ($request->has('email')) {
                $query->where('email', $request->email);
            }

            $reservations = $query->orderBy('date', 'desc')
                                  ->orderBy('heure', 'desc')
                                  ->get();

            return response()->json([
                'success' => true,
                'data' => $reservations,
                'total' => $reservations->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des réservations'
            ], 500);
        }
    }


    public function show($id){
        try {
            $reservation = Reservation::with('service.prestataire')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $reservation
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation non trouvée'
            ], 404);
        }
    }







}
