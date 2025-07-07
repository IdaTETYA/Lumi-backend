<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Message extends Model
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'id_message';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    protected $table = 'message';


    protected  $fillable =
        [   'id_message',
            'chat_ai_id',
            'parent_message_id',
            'content',
            'role',
            'created_at',
            'updated_at',
            'deleted_at',
            'user_id'
        ];


    public function chat(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function chatAi()
    {
        return $this->belongsTo(ChatAI::class, 'chat_ai_id', 'id_chat_ai');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

 }
