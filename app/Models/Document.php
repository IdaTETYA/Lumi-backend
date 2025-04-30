<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Document extends Model
{
    use HasFactory, Notifiable, softDeletes;

    protected $primaryKey = 'id_document';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    protected $table = 'document';

    protected $fillable =
        [
            'id_document',
            'type',
            'titre',
            'file',
            'medecin_id',
            'valide_par_id',
            'statut',
            'motif_refus',

        ];

    public function medecin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medecin_id');
    }

    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par_id');
    }

}
