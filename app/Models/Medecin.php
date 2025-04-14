<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Medecin extends Model
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'id_medecin';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    protected $fillable =
        [
            'id_medecin',
            'specialite',
            'numeroONMC',
            'lieu_de_travail',
            'latitude_lieu_de_travail',
            'longitude_lieu_de_travail',
            'status',
            'date_de_naissance',
            'user_id'

        ];


    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);

    }

    public function document(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function rendeVous()
    {
        return $this->hasMany(RendezVous::class);
    }

    public function consultation(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Consultation::class);
    }

}
