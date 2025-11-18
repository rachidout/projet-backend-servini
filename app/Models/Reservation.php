<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_prestataire',
        'id_service',
        'client_nom',
        'client_prenom',
        'client_email',
        'client_telephone',
        'client_adresse',
        'description_du_besoin',
        'date',
        'heure', 
        'statut'
    ];

    protected $casts = [
        'date' => 'date',
        'heure' => 'datetime:H:i', // ✅ Correction: utilise datetime au lieu de time
    ];

    // ✅ Relation avec le prestataire (spécifier la clé étrangère)
    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class, 'id_prestataire');
    }

    // ✅ Relation avec le service
    public function service()
    {
        return $this->belongsTo(Service::class, 'id_service');
    }

    // ✅ Accesseur pour le nom complet du client (corriger les noms de colonnes)
    public function getNomCompletAttribute()
    {
        return $this->client_prenom . ' ' . $this->client_nom;
    }

    // Scopes pour filtrer par statut
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeConfirmees($query)
    {
        return $query->where('statut', 'confirmee');
    }

    public function scopeTerminees($query)
    {
        return $query->where('statut', 'terminee');
    }
}