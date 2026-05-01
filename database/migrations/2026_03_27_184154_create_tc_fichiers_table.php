<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration tc_fichiers — Corrigée :
 *  - type_valeur : string(2) au lieu de unsignedTinyInteger
 *    (les codes SIBTEL sont des chaînes '10', '20', '30', '84', etc.)
 *  - code_enregistrement : string(2) pour les valeurs '21', '22'
 *  - ajout de champs utiles pour le suivi
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tc_fichiers', function (Blueprint $table) {
            $table->id();

            // ── Identification du fichier ──────────────────────────
            $table->string('nom_fichier', 200)
                  ->comment('Nom original du fichier ex: 26-999-10-21-reel.ENV');
            $table->string('chemin_complet', 500)
                  ->comment('Chemin absolu du fichier sur le serveur');

            // ── Type SIBTEL — CORRIGÉ : string(2) pas tinyInteger ──
            $table->string('type_valeur', 2)->default('10')
                  ->comment('10=Virement, 20=Prélèvement, 30-33=Chèque, 40-43=LDC, 82-84=CNP/ARP/Papillon');
            $table->string('code_enregistrement', 2)->nullable()
                  ->comment('21=Présentation, 22=Rejet');

            // ── Informations du fichier ────────────────────────────
            $table->string('sens', 1)->nullable()
                  ->comment('1=Aller (ENVX), 2=Retour (RCPX)');
            $table->string('code_devise', 3)->nullable()->default('788')
                  ->comment('788=TND, 840=USD, 978=EUR');
            $table->string('date_operation', 8)->nullable()
                  ->comment('Date opération format DDMMYYYY du fichier T24');

            // ── Statut de traitement ───────────────────────────────
            $table->string('statut', 20)->default('RECU')
                  ->comment('RECU, EN_COURS, TRAITE, TRAITE_PARTIEL, ERREUR');

            // ── Statistiques du fichier ────────────────────────────
            $table->unsignedInteger('nb_transactions')->default(0)
                  ->comment('Nombre de transactions dans le fichier');
            $table->unsignedInteger('nb_rejets')->default(0)
                  ->comment('Nombre de rejets détectés');
            $table->decimal('montant_total', 15, 3)->default(0)
                  ->comment('Montant total en TND');

            // ── Timestamps ────────────────────────────────────────
            $table->timestamp('date_reception')->useCurrent()
                  ->comment('Date/heure de réception du fichier');
            $table->timestamps();

            // ── Index ─────────────────────────────────────────────
            $table->index('type_valeur');
            $table->index('statut');
            $table->index('date_reception');
            $table->index('code_enregistrement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tc_fichiers');
    }
};