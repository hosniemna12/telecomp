<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TcCommentaire extends Model
{
    protected $table = 'TC_COMMENTAIRES';

    protected $fillable = [
        'fichier_id', 'user_id', 'contenu', 'type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function fichier()
    {
        return $this->belongsTo(TcFichier::class, 'fichier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
