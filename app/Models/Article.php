<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Article extends Model
{
    use HasFactory, Notifiable, softDeletes;

    protected $fillable =
        [
            'id_article',
            'titre',
            'categorie',
            'resume',
            'image',
            'contenu',
            'statut',
            'medecin_id',
            'created_at',
            'updated_at',
            'deleted_at',
            'date_publication',
            'supprime_par_id'



        ];
}
