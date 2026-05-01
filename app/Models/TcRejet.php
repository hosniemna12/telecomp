<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TcRejet extends Model
{
    protected $table = 'tc_rejets';

    protected $fillable = [
        'fichier_id',
        'detail_id',
        'code_rejet',
        'motif_rejet',
        'etape_detection',
        'traite',
        'date_traitement',
    ];

    protected $casts = [
        'traite'          => 'boolean',
        'date_traitement' => 'datetime',
    ];

    public function fichier(): BelongsTo
    {
        return $this->belongsTo(TcFichier::class, 'fichier_id');
    }

    public function detail(): BelongsTo
    {
        return $this->belongsTo(TcEnrDetail::class, 'detail_id');
    }

    // Scopes
    public function scopeNonTraites($query)
    {
        return $query->where('traite', false);
    }

    public function scopeTraites($query)
    {
        return $query->where('traite', true);
    }

    public function scopeValidation($query)
    {
        return $query->where('etape_detection', 'VALIDATION');
    }

    public function scopeParsing($query)
    {
        return $query->where('etape_detection', 'PARSING');
    }

    // Libellé de l'étape
    public function getLibelleEtapeAttribute(): string
    {
        return match($this->etape_detection) {
            'PARSING'    => 'Parsing',
            'VALIDATION' => 'Validation',
            'SYSTEME'    => 'Système',
            default      => $this->etape_detection ?? '?',
        };
    }
}