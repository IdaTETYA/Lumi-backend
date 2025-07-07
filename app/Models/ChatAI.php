<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatAI extends Model
{
    use SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = 'id_chat_ai';
    public $timestamps = true;
    protected $table = 'chat_ai';

    protected $fillable = [
        'id_chat_ai',
        'title',
        'patient_id',
        'symptoms',
        'conseil',
        'analyse',
    ];

    protected $casts = [
        'symptoms' => 'array',
    ];

    public function patient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id', 'id_user');
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Message::class, 'chat_ai_id', 'id_chat_ai');
    }

    public function consultation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'consultation_id', 'id_consultation');
    }


    public function transmissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TransmissionMedecin::class, 'chat_ai_id', 'id_chat_ai');
    }

}


