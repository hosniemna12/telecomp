<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TcFichier extends Model
{
    protected $table = 'TC_FICHIERS';

    protected $fillable = [
        'nom_fichier', 'chemin_complet', 'type_valeur',
        'code_enregistrement', 'sens', 'code_devise',
        'date_operation', 'statut', 'nb_transactions',
        'nb_rejets', 'montant_total', 'date_reception',
        'uploaded_by', 'valide_par', 'date_validation',
        'commentaire_rejet',
    ];

    protected $casts = [
        'date_reception'  => 'datetime',
        'date_validation' => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'montant_total'   => 'decimal:3',
    ];

    // Relations existantes
    public function enregistrementsDetails()
    {
        return $this->hasMany(TcEnrDetail::class, 'fichier_id');
    }

    public function enregistrementsGlobaux()
    {
        return $this->hasMany(TcEnrGlobal::class, 'fichier_id');
    }

    public function rejets()
    {
        return $this->hasMany(TcRejet::class, 'fichier_id');
    }

    public function xmlProduit()
    {
        return $this->hasOne(TcXmlProduit::class, 'fichier_id');
    }

    // Nouvelles relations
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function valideur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function commentaires()
    {
        return $this->hasMany(TcCommentaire::class, 'fichier_id');
    }

    public function notifications()
    {
        return $this->hasMany(TcNotification::class, 'fichier_id');
    }

    // Scopes utiles
    public function scopeEnAttenteValidation($query)
    {
        return $query->where('statut', 'EN_ATTENTE_VALIDATION');
    }

    public function scopeParOperateur($query, int $userId)
    {
        return $query->where('uploaded_by', $userId);
    }
}
