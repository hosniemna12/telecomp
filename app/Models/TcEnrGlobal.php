<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    protected $casts = [
        'montant_total_virements' => 'float',
        'nombre_total_virements'  => 'integer',
    ];

    public function fichier(): BelongsTo
    {
        return $this->belongsTo(TcFichier::class, 'fichier_id');
    }

    // Date DDMMYYYY → YYYY-MM-DD
    public function getDateOperationIsoAttribute(): string
    {
        $d = $this->date_operation ?? '';
        if (strlen($d) === 8 && is_numeric($d)) {
            return substr($d, 4, 4) . '-' . substr($d, 2, 2) . '-' . substr($d, 0, 2);
        }
        return $d;
    }

    public function getLibelleTypeAttribute(): string
    {
        return match($this->code_valeur) {
            '10'    => 'Virement',
            '20'    => 'Prélèvement',
            '30'    => 'Chèque (1ère présentation)',
            '31'    => 'Chèque CNP',
            '32'    => 'Chèque ARP',
            '33'    => 'Chèque retour',
            '40'    => 'Lettre de change',
            '41'    => 'LDC acceptée',
            '42'    => 'Billet à ordre',
            '43'    => 'Billet à ordre avalisé',
            '82'    => 'CNP complet',
            '83'    => 'ARP complet',
            '84'    => 'Papillon',
            default => 'Type ' . ($this->code_valeur ?? '?'),
        };
    }
}