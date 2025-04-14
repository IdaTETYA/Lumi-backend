<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Document extends Model
{
    use HasFactory, Notifiable, softDeletes;

    protected $fillable =
        [
            'id_document',
            'type',
            'titre',
            'chemin',
            'medecin_id',
            'valide_par_id',
            'statut',

        ];

    public function medecin()
    {
        return $this->belongsTo(Medecin::class, 'medecin_id');
    }

}
