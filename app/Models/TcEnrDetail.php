<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TcEnrDetail extends Model
{
    protected $table = 'tc_enr_details';

    protected $fillable = [
        'fichier_id',
        'numero_virement',
        'montant',
        'rib_donneur',
        'nom_donneur',
        'rib_beneficiaire',
        'nom_beneficiaire',
        'code_institution_dest',
        'motif_operation',
        'reference_dossier',
        'situation_donneur',
        'type_compte_donneur',
        'code_rejet',
        'statut',
    ];

    // Relation inverse → appartient à un fichier
    public function fichier()
    {
        return $this->belongsTo(TcFichier::class, 'fichier_id');
    }

    // Relation → a plusieurs rejets
    public function rejets()
    {
        return $this->hasMany(TcRejet::class, 'detail_id');
    }
}