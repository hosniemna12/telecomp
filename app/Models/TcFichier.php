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

    // Scopes utiles
    public function scopeTraites($query)
    {
        return $query->where('statut', 'TRAITE');
    }

    public function scopeErreurs($query)
    {
        return $query->where('statut', 'ERREUR');
    }
}