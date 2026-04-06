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
    Schema::create('tc_enr_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('fichier_id')->constrained('tc_fichiers')->onDelete('cascade');
        $table->unsignedInteger('numero_virement');
        $table->decimal('montant', 15, 3);
        $table->string('rib_donneur', 20)->nullable();
        $table->string('nom_donneur', 30)->nullable();
        $table->string('rib_beneficiaire', 20)->nullable();
        $table->string('nom_beneficiaire', 30)->nullable();
        $table->unsignedSmallInteger('code_institution_dest')->nullable();
        $table->string('motif_operation', 45)->nullable();
        $table->string('reference_dossier', 20)->nullable();
        $table->unsignedTinyInteger('situation_donneur')->nullable();
        $table->unsignedTinyInteger('type_compte_donneur')->nullable();
        $table->unsignedTinyInteger('code_rejet')->nullable();
        $table->string('statut', 20)->default('EN_ATTENTE');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tc_enr_details');
    }
};
