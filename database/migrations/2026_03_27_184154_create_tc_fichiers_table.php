<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('tc_fichiers', function (Blueprint $table) {
        $table->id();
        $table->string('nom_fichier', 200);
        $table->string('chemin_complet', 500);
        $table->unsignedTinyInteger('type_valeur');
        $table->unsignedTinyInteger('code_enregistrement');
        $table->string('sens', 10)->nullable();
        $table->string('statut', 20)->default('RECU');
        $table->string('code_devise', 3)->nullable();
        $table->timestamp('date_reception')->useCurrent();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tc_fichiers');
    }
};
