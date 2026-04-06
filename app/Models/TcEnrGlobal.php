<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TcEnrGlobal extends Model
{
    protected $table = 'tc_enr_globaux';

    protected $fillable = [
        'fichier_id',
        'sens',
        'code_valeur',
        'nature_remettant',
        'code_remettant',
        'code_centre_regional',
        'date_operation',
        'numero_lot',
        'code_devise',
        'montant_total_virements',
        'nombre_total_virements',
    ];

    // Relation inverse → appartient à un fichier
    public function fichier()
    {
        return $this->belongsTo(TcFichier::class, 'fichier_id');
    }
}