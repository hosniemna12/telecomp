<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'statut',
        'created_at',
    ];

    protected $casts = [
        'created_at'    => 'datetime',
        'donnees_avant' => 'array',
        'donnees_apres' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSuccess($query)
    {
        return $query->where('statut', 'SUCCESS');
    }

    public function scopeFailed($query)
    {
        return $query->where('statut', 'FAILED');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
