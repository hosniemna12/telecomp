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
    Schema::create('tc_enr_globaux', function (Blueprint $table) {
        $table->id();
        $table->foreignId('fichier_id')->constrained('tc_fichiers')->onDelete('cascade');
        $table->unsignedTinyInteger('sens');
        $table->unsignedTinyInteger('code_valeur');
        $table->unsignedTinyInteger('nature_remettant');
        $table->unsignedTinyInteger('code_remettant');
        $table->unsignedSmallInteger('code_centre_regional');
        $table->string('date_operation', 8);
        $table->unsignedSmallInteger('numero_lot');
        $table->string('code_devise', 3)->nullable();
        $table->decimal('montant_total_virements', 15, 3)->default(0);
        $table->unsignedInteger('nombre_total_virements')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tc_enr_globaux');
    }
};
