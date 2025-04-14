<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Consultation extends Model
{
    use HasFactory, Notifiable;

    protected $fillable =
        [
            'id_consultation',
            'date',
            'heure_debut',
            'heure_fin',
            'motif',
            'statut',
            'description',
            'type_consultation',
            'medecin_id',
            'patient_id'
        ];

    public function medecin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Medecin::class, 'medecin_id');
    }

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }



}
