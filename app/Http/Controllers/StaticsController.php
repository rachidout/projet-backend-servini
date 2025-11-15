<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Prestataire;
use App\Models\Reservation;
use App\Models\User;

class StaticsController extends Controller
{
    public function index()
    {
        return response()->json([
            'total_services' => Service::count(),
            'total_prestations' => Prestataire::count(),
            'total_reservations' => Reservation::count(),
            'total_clients' => Reservation::distinct('client_nom')->count('client_nom')
        ]);
    }
}
