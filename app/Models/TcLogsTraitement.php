<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TcLogsTraitement extends Model
{
    protected $table = 'tc_logs_traitement';

    public $timestamps = false;

    protected $fillable = [
        'fichier_id',
        'etape',
        'niveau',
        'message',
        'donnees_contexte',
        'created_at',
    ];

    protected $casts = [
        'donnees_contexte' => 'array',
        'created_at'       => 'datetime',
    ];

    // Ajouter created_at automatiquement
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    public function scopeErrors($query)
    {
        return $query->where('niveau', 'ERROR');
    }

    public function scopeWarnings($query)
    {
        return $query->where('niveau', 'WARNING');
    }

    public function scopeInfos($query)
    {
        return $query->where('niveau', 'INFO');
    }

    public function fichier()
    {
        return $this->belongsTo(TcFichier::class, 'fichier_id');
    }
}