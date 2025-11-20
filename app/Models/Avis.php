<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    protected $fillable= [
        'id_reservation',
        'note',
        'commentaire',
    ];
  
  public function prestataire() {
    return $this->hasOneThrough(Prestataire::class, Reservation::class, 'id', 'id', 'id_reservation', 'id_prestataire');
  }
  public function reservation()
  {
      return $this->belongsTo(Reservation::class, 'id_reservation', 'id');
      //                                           ^^^^^^^^^^^^^^  ^^^^^
      //                                           Foreign key     Primary key
  }

}
