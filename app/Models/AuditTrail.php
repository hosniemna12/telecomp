<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    protected $table = 'audit_trail';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_email',
        'action',
        'module',
        'description',
        'ip_address',
        'donnees_avant',
        'donnees_apres',
        'statut_action',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}