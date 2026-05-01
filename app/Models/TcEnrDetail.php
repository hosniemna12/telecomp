<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle TcEnrDetail — Enregistrement détail de télécompensation
 *
 * Stocke les champs de TOUS les types de valeur SIBTEL :
 * virement (10), prélèvement (20), chèques (30-33, 82-83), LDC (40-43), papillon (84)
 *
 * IMPORTANT : montant est en TND (decimal 15,3), pas en millimes.
 *             La division par 1000 est faite au parsing dans EnvParserService.
 */
class TcEnrDetail extends Model
{
    protected $table = 'tc_enr_details';

    protected $fillable = [
        // ── Identification commune ─────────────────────────────────
        'fichier_id',
        'type_valeur',
        'numero_virement',
        'code_enregistrement',

        // ── Montant (TND, decimal 15,3) ───────────────────────────
        'montant',

        // ── RIBs et noms ──────────────────────────────────────────
        'rib_donneur',
        'nom_donneur',
        'rib_beneficiaire',
        'nom_beneficiaire',
        'code_institution_dest',
        'code_centre_dest',

        // ── Virement (type 10) ─────────────────────────────────────
        'reference_dossier',
        'code_enreg_comp',
        'nb_enreg_comp',
        'motif_operation',
        'date_compensation',
        'motif_rejet',
        'situation_donneur',
        'type_compte',
        'nature_compte',
        'existence_dossier',
        'zone_libre',
        'code_suivi',

        // ── Prélèvement (type 20) ─────────────────────────────────
        'rib_payeur',
        'rib_creancier',
        'code_emetteur',
        'ref_contrat',
        'libelle_prelevement',
        'date_echeance',
        'code_payeur',
        'code_maj',
        'date_maj',

        // ── Chèques (types 30, 31, 32, 33, 82, 83) ───────────────
        'rib_tireur',
        'numero_cheque',
        'date_emission',
        'lieu_emission',
        'situation_beneficiaire',
        'date_cnp',
        'numero_cnp',
        'code_devise_position',
        'montant_reclame',
        'montant_provision',
        'montant_regularise',
        'date_preaviss',
        'date_presentation',
        'date_delivrance',
        'signature_electronique',
        'ref_cle_publique',
        'img_recto',
        'img_verso',
        'date_doc_joint',
        'numero_doc_joint',
        'code_valeur_doc_joint',
        'motif_rejet_doc_joint',

        // ── Papillon (type 84) ─────────────────────────────────────
        'date_etablissement',
        'numero_papillon',

        // ── Lettres de change (types 40-43) ───────────────────────
        'numero_lettre_change',
        'rib_tire',
        'rib_tire_initial',
        'rib_cedant',
        'nom_cedant',
        'nom_tire',
        'ref_commerciales_tire',
        'ref_commerciales_tireur',
        'montant_interets',
        'montant_frais_protest',
        'code_acceptation',
        'code_endossement',
        'date_echeance_ldc',
        'date_echeance_initiale',
        'date_creation',
        'lieu_creation',
        'code_ordre_payer',
        'situation_cedant',
        'code_risque_bct',
        'messages',

        // ── Statut ─────────────────────────────────────────────────
        'code_rejet',
        'statut',
    ];

    protected $casts = [
        'montant'              => 'float',
        'montant_reclame'      => 'float',
        'montant_provision'    => 'float',
        'montant_regularise'   => 'float',
        'montant_interets'     => 'float',
        'montant_frais_protest' => 'float',
        'nb_enreg_comp'        => 'integer',
    ];

    // ── Relations ─────────────────────────────────────────────────

    public function fichier(): BelongsTo
    {
        return $this->belongsTo(TcFichier::class, 'fichier_id');
    }

    public function rejets(): HasMany
    {
        return $this->hasMany(TcRejet::class, 'detail_id');
    }

    // ── Accesseurs utiles ─────────────────────────────────────────

    /**
     * Retourne le RIB débiteur selon le type de valeur.
     * Utilise le champ spécifique du type en priorité.
     */
    public function getRibDebiteurAttribute(): string
    {
        return match($this->type_valeur) {
            '20'        => $this->rib_payeur ?? $this->rib_donneur ?? '',
            '30', '31', '32', '33', '82', '83', '84'
                        => $this->rib_tireur ?? $this->rib_donneur ?? '',
            '40', '41', '42', '43'
                        => $this->rib_tire   ?? $this->rib_donneur ?? '',
            default     => $this->rib_donneur ?? '',
        };
    }

    /**
     * Retourne le RIB créancier selon le type de valeur.
     */
    public function getRibCreancierAttribute(): string
    {
        return match($this->type_valeur) {
            '20'        => $this->rib_creancier ?? $this->rib_beneficiaire ?? '',
            '40', '41', '42', '43'
                        => $this->rib_cedant    ?? $this->rib_beneficiaire ?? '',
            default     => $this->rib_beneficiaire ?? '',
        };
    }

    /**
     * Retourne le libellé du type de valeur.
     */
    public function getLibelleTypeAttribute(): string
    {
        return match($this->type_valeur) {
            '10'        => 'Virement',
            '20'        => 'Prélèvement',
            '30'        => 'Chèque (1ère présentation)',
            '31'        => 'Chèque CNP (paiement partiel)',
            '32'        => 'Chèque ARP (après ARP)',
            '33'        => 'Chèque retour',
            '40'        => 'Lettre de change',
            '41'        => 'Lettre de change acceptée',
            '42'        => 'Billet à ordre',
            '43'        => 'Billet à ordre avalisé',
            '82'        => 'CNP (certificat non-paiement)',
            '83'        => 'ARP (attestation reconstitution provision)',
            '84'        => 'Papillon',
            default     => 'Type ' . $this->type_valeur,
        };
    }

    /**
     * Indique si cet enregistrement contient un rejet.
     */
    public function hasRejet(): bool
    {
        return !empty($this->motif_rejet) && $this->motif_rejet !== '00000000';
    }
}