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
    Schema::create('tc_xml_produits', function (Blueprint $table) {
        $table->id();
        $table->foreignId('fichier_id')->constrained('tc_fichiers')->onDelete('cascade');
        $table->string('type_message', 20);
        $table->longText('contenu_xml');
        $table->string('chemin_archive', 500)->nullable();
        $table->boolean('valide_xsd')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tc_xml_produits');
    }
};
