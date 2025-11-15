<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Prestataire extends Authenticatable
{
        use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'image',
        'prix_heure',
        'bio',
        'ville',
        'zone',
        'password',
        'carte_identite',
        'status',
        'note_moyenne',
        'facebook_url',
        'linkedin_url',
    ];
    protected $hiddern = [
        'password',
    ];

    function service(){
        return $this->hasOne(Service::class,'id_prestataire');
    }
    function reservations(){
        return $this->hasMany(Reservation::class,'id_prestataire');
    }
    function avis(){
        //Prestataire -> Reservation -> Avis ... donc ra y9dr ywsl liha
        return $this->hasManyThrough(Avis::class , Reservation::class , 'id_prestataire' , 'id_reservation');
    }
}
