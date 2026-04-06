<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TcXmlProduit extends Model
{
    protected $table = 'tc_xml_produits';

    protected $fillable = [
        'fichier_id',
        'type_message',
        'contenu_xml',
        'chemin_archive',
        'valide_xsd',
    ];

    // Cast automatique
    protected $casts = [
        'valide_xsd' => 'boolean',
    ];

    // Relation → appartient à un fichier
    public function fichier()
    {
        return $this->belongsTo(TcFichier::class, 'fichier_id');
    }
}