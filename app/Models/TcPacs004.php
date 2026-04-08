<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TcPacs004 extends Model
{
    protected $table = 'tc_pacs004';

    protected $fillable = [
        'fichier_id',
        'rejet_id',
        'msg_id',
        'cre_dt_tm',
        'nb_of_txs',
        'sttlm_mtd',
        'clr_sys_prtry',
        'instg_agt_bic',
        'rtr_id',
        'orgnl_end_to_end_id',
        'orgnl_tx_id',
        'orgnl_tx_ref',
        'rtr_intr_bk_sttlm_amt',
        'devise',
        'chrg_br',
        'dbtr_nm',
        'dbtr_acct_id',
        'dbtr_agt_bic',
        'cdtr_nm',
        'cdtr_acct_id',
        'cdtr_agt_bic',
        'motif_rejet',
        'libelle_rejet',
        'contenu_xml',
        'valide_xsd',
        'statut',
    ];

    protected $casts = [
        'cre_dt_tm'              => 'datetime',
        'valide_xsd'             => 'boolean',
        'rtr_intr_bk_sttlm_amt' => 'decimal:3',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function fichier()
    {
        return $this->belongsTo(TcFichier::class, 'fichier_id');
    }

    public function rejet()
    {
        return $this->belongsTo(TcRejet::class, 'rejet_id');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeGeneres($query)
    {
        return $query->where('statut', 'GENERE');
    }

    public function scopeEnvoyes($query)
    {
        return $query->where('statut', 'ENVOYE');
    }
}