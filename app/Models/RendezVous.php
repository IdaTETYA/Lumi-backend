<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class RendezVous extends Model
{
    use  hasFactory, notifiable, softDeletes;
    protected $fillable =
        [
            'id_rendez_vous',
            'date',
            'statut',
            'patient_id',
            'medecin_id'
        ];

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function medecin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Medecin::class);

    }


}
