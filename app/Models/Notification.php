<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Notification extends Model
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'id_notification';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable =
    [
        'id_notification',
        'contenu',
        'dateTime',
        'status',
        'destinataire_id',
        'type_notification_id'
    ];

    public function destinataire(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public  function  typeNotification()
    {
        return $this->belongsTo(TypeNotification::class);
    }





}

