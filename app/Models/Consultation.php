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

    protected $casts = [
        'date' => 'datetime',
        'heure_debut' => 'datetime',
        'heure_fin' => 'datetime',
        'statut' => 'boolean',
    ];

    public function medecin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Medecin::class, 'medecin_id');
    }

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function rendezVous(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RendezVous::class, 'rendez_vous_id', 'id_rendez_vous');
    }

    public function typeConsultation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TypeConsultation::class, 'type_consultation_id', 'id_type_consultation');
    }

    public function chatAi(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ChatAI::class, 'consultation_id', 'id_consultation');
    }

    public function transmission(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TransmissionMedecin::class, 'consultation_id', 'id_consultation');
    }



}
