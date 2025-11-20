<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Avis; // <-- AJOUT DU MODÈLE AVIS

class StaticsController extends Controller{
    public function index(){
        return response()->json([
            'total_services' => Service::count(),
            'total_prestations' => Reservation::where('statut', 'confirmee')
        ->count(),
            'total_reservations' => Reservation::count(),
            'total_clients' => Reservation::distinct('client_nom')->count('client_nom'),
            'total_prestataires' => Prestataire::count()
        ]);
    }
    

    public function staticsprestataire(){
        $prestataire = Auth::user(); 
        
        // Vérifiez si l'utilisateur est authentifié et a un ID
        if (!$prestataire || !$prestataire->id) {
            return response()->json(['message' => 'Non autorisé ou prestataire non identifié.'], 401);
        }
        
        $prestataireId = $prestataire->id; 

        // Compter les avis liés aux réservations de ce prestataire
        $totalAvis = Avis::whereHas('reservation', function ($query) use ($prestataireId) {
            $query->where('id_prestataire', $prestataireId);
        })->count();

        return response()->json([
            // Nombre de services proposés par ce prestataire
            'total_services' => 1, // Un prestataire a généralement 1 service
            
            // Total de prestations (réservations confirmées) pour ce prestataire
            'total_prestations' => Reservation::where('id_prestataire', $prestataireId)
                ->where('statut', 'confirmee')
                ->count(),
            
            // Total de réservations (tous statuts) pour ce prestataire
            'total_reservations' => Reservation::where('id_prestataire', $prestataireId)
                ->count(),
            
            // Nombre de clients uniques ayant réservé avec ce prestataire
            'total_clients' => Reservation::where('id_prestataire', $prestataireId)
                ->distinct('client_email') 
                ->count('client_email'),
                
            'total_commentaires' => $totalAvis // <-- NOUVELLE STATISTIQUE AJOUTÉE
        ]);
    }

}