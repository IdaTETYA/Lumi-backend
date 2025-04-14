<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TypeNotification extends Model
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'id_patient';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable =
        [
            'id_type_notication',
            'nom',
            'description',
            'icone',
        ];


    public function notication(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class, 'type_notification_id');
    }
}
