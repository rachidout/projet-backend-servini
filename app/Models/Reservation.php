<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
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
public function prestataire(){
    return $this->belongsTo(Prestataire::class , 'id_prestataire');
}
  public function service() {
    return $this->belongsTo(Service::class,'id_service');
  }
  public function avis() {
    return $this->hasOne(Avis::class ,'id_reservation');//drt hasOne 7it kola client i9der idir ghir wa7ed commentaire.
  }
}
