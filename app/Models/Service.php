<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
         'id_prestataire',
         'categorie'
    ];
    public  function prestataire() {
        return $this->belongsTo(Prestataire::class , 'id_prestataire');
    }
    public function reservations() {
        return $this->hasMany(Reservation::class,'id_service');
    }
}
