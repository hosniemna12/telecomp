<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TcXmlProduit — XML ISO 20022 généré par le transformeur
 * CORRIGÉ : ajout chemin_archive dans fillable, cast valide_xsd boolean
 */
class TcXmlProduit extends Model
{
    protected $table = 'tc_xml_produits';

    protected $fillable = [
        'fichier_id',
        'type_message',   // ex: pacs.008.001.10, pacs.003.001.09
        'contenu_xml',
        'chemin_archive', // chemin fichier archivé sur disque (optionnel)
        'valide_xsd',
    ];

    protected $casts = [
        'valide_xsd' => 'boolean',
    ];

    public function fichier(): BelongsTo
    {
        return $this->belongsTo(TcFichier::class, 'fichier_id');
    }

    // Libellé du type de message
    public function getLibelleTypeMessageAttribute(): string
    {
        return match($this->type_message) {
            'pacs.008.001.10' => 'Virement (pacs.008)',
            'pacs.003.001.09' => 'Prélèvement/Chèque/LDC (pacs.003)',
            'pacs.004.001.11' => 'Rejet (pacs.004)',
            default           => $this->type_message ?? '?',
        };
    }

    // Taille du XML en KB
    public function getTailleKbAttribute(): float
    {
        return round(strlen($this->contenu_xml ?? '') / 1024, 2);
    }
}