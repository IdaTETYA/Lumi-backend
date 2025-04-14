<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_patient';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;


    protected $fillable =

        [
            'id_patient',
            'stade_de_grossesse',
            'user_id'
        ];


    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function rendezVous(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RendezVous::class);
    }

    public  function consultation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Consultation::class);
    }






}
