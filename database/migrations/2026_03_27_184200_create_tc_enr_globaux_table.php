<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tc_enr_globaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fichier_id')
                  ->constrained('tc_fichiers')
                  ->onDelete('cascade');

            // CORRIGÉ : string au lieu de unsignedTinyInteger/SmallInteger
            $table->string('sens', 1)->default('1');
            $table->string('code_valeur', 2)->default('10');
            $table->string('nature_remettant', 1)->default('1');
            $table->string('code_remettant', 2)->default('26');
            $table->string('code_centre_regional', 3)->default('999');
            $table->string('date_operation', 8)->default('');
            $table->string('numero_lot', 4)->default('0001');
            $table->string('code_devise', 3)->nullable()->default('TND');

            $table->decimal('montant_total_virements', 15, 3)->default(0);
            $table->unsignedInteger('nombre_total_virements')->default(0);

            $table->timestamps();
            $table->index('fichier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tc_enr_globaux');
    }
};