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
  public function reservation() {
    return $this->belongsTo(Reservation::class,'id_reservation');
  }
}
