<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{

    use HasFactory, Notifiable, hasApiTokens;
    protected $primaryKey = 'id_user';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id_user',
        'nom',
        'prenom',
        'date_de_naissance',
        'sexe',
        'ville',
        'quartier',
        'numero_telephone',
        'email',
        'password',
        'role',
        'specialite',
        'numeroONMC',
        'lieu_de_travail',
        'latitude_lieu_de_travail',
        'longitude_lieu_de_travail',
        'remember_token',
        'stade_de_grossesse',
        'statut_compte',
        'est_connecte',
        'device_token',
        'recevoir_notifications',
        'theme',
        'derniere_connexion',
        'email_verifie_at',
        'accepte_conditions',
        'derniere_activite',
        'nombre_connexions',
        'created_at',
        'updated_at',
        'motif_refus'





    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function patient(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public  function medecin(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Medecin::class);

    }

    public  function administrateur(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Administrateur::class);
    }

    public  function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function chats(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_users','user_id','chat_id')
                    ->withPivot('joined_at');
    }

    public function messagesEnvoyes()
    {
        return $this->hasMany(Message::class);

    }

    public function messagesRecus()
    {
        $chatIds=$this->chats()->pluck('chats.id');
    }


    public function is_patient()
    {
        return $this->role === 'patient';

    }

    public function is_medecin()
    {
        return $this->role === 'medecin';
    }

    public function is_adminstrateur()
    {
        return $this->role === 'administrateur';
    }




}


