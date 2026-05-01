<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TcNotification extends Model
{
    protected $table = 'TC_NOTIFICATIONS';

    protected $fillable = [
        'user_id', 'titre', 'message', 'type', 'lu', 'fichier_id',
    ];

    protected $casts = [
        'lu'         => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fichier()
    {
        return $this->belongsTo(TcFichier::class, 'fichier_id');
    }

    // Notifier tous les superviseurs et admins
    public static function notifierSuperviseurs(string $titre, string $message, int $fichierId): void
    {
        $destinataires = User::whereIn('role', ['superviseur', 'admin'])->get();
        foreach ($destinataires as $user) {
            self::create([
                'user_id'    => $user->id,
                'titre'      => $titre,
                'message'    => $message,
                'type'       => 'UPLOAD',
                'fichier_id' => $fichierId,
            ]);
        }
    }

    // Notifier l'opérateur qui a uploadé
    public static function notifierOperateur(int $userId, string $titre, string $message, int $fichierId): void
    {
        self::create([
            'user_id'    => $userId,
            'titre'      => $titre,
            'message'    => $message,
            'type'       => 'VALIDATION',
            'fichier_id' => $fichierId,
        ]);
    }
}
