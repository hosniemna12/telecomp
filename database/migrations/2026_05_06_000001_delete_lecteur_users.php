<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Supprime tous les utilisateurs avec le rôle 'lecteur'
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'lecteur')->delete();
    }

    /**
     * Reverse the migrations.
     * Note: Les utilisateurs supprimés ne peuvent pas être restaurés
     */
    public function down(): void
    {
        // Les utilisateurs supprimés ne peuvent pas être restaurés
        // Cette migration est irréversible par design
    }
};
