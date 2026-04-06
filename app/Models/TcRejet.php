<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    // Cast automatique
    protected $casts = [
        'traite'           => 'boolean',
        'date_traitement'  => 'datetime',
    ];

    // Relation → appartient à un fichier
    public function fichier()
    {
        return $this->belongsTo(TcFichier::class, 'fichier_id');
    }

    // Relation → appartient à un détail
    public function detail()
    {
        return $this->belongsTo(TcEnrDetail::class, 'detail_id');
    }
}