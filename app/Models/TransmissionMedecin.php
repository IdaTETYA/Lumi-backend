<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransmissionMedecin extends Model
{
    use HasFactory;

    protected $table = 'transmissions_medecin';
    protected $primaryKey = 'id_transmission';
    public $incrementing = true;

    protected $fillable = [
        'patient_id',
        'chat_ai_id',
        'symptomes',
        'maladie_predite',
        'confiance',
        'priorite',
        'rapport_complet',
        'statut',
    ];

    protected $casts = [
        'symptomes' => 'array',
        'rapport_complet' => 'array',
        'confiance' => 'float',
    ];

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id', 'id_user');
    }

    public function chatAi(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChatAI::class, 'chat_ai_id', 'id_chat_ai');
    }

    public function consultation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'consultation_id', 'id_consultation');
    }
}
