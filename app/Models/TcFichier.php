<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TcFichier extends Model
{
    protected $table = 'tc_fichiers';

    protected $fillable = [
        'nom_fichier',
        'chemin_complet',
        'type_valeur',
        'code_enregistrement',
        'sens',
        'statut',
        'code_devise',
        'date_reception',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function enrGlobaux()
    {
        return $this->hasMany(TcEnrGlobal::class, 'fichier_id');
    }

    public function enrDetails()
    {
        return $this->hasMany(TcEnrDetail::class, 'fichier_id');
    }

    public function rejets()
    {
        return $this->hasMany(TcRejet::class, 'fichier_id');
    }

    public function xmlProduits()
    {
        return $this->hasMany(TcXmlProduit::class, 'fichier_id');
    }

    public function logs()
    {
        return $this->hasMany(TcLogsTraitement::class, 'fichier_id');
    }

    public function pacs004()
    {
        return $this->hasMany(TcPacs004::class, 'fichier_id');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeTraites($query)
    {
        return $query->where('statut', 'TRAITE');
    }

    public function scopeErreurs($query)
    {
        return $query->where('statut', 'ERREUR');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'EN_ATTENTE');
    }

    public function scopeEnCours($query)
    {
        return $query->where('statut', 'EN_COURS');
    }

    // ── Accesseurs utiles ─────────────────────────────────────────

    public function getLibelleTypeValeurAttribute(): string
    {
        return match($this->type_valeur) {
            '20'         => 'Prélèvement',
            '30'         => 'Chèque',
            '31'         => 'Chèque certifié',
            '32'         => 'Chèque de banque',
            '33'         => 'Chèque visé',
            '40'         => 'Lettre de change',
            '41'         => 'Lettre de change acceptée',
            '42'         => 'Billet à ordre',
            '43'         => 'Billet à ordre avalisé',
            '60', '61'   => 'Virement',
            default      => 'Type ' . $this->type_valeur,
        };
    }

    public function getLibelleStatutAttribute(): string
    {
        return match($this->statut) {
            'EN_ATTENTE' => 'En attente',
            'EN_COURS'   => 'En cours',
            'TRAITE'     => 'Traité',
            'ERREUR'     => 'Erreur',
            default      => $this->statut,
        };
    }

    public function hasPacs004(): bool
    {
        return $this->pacs004()->exists();
    }
}