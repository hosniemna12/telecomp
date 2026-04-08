<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tc_pacs004', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fichier_id')
                  ->constrained('tc_fichiers')
                  ->onDelete('cascade');
            $table->foreignId('rejet_id')
                  ->nullable()
                  ->constrained('tc_rejets')
                  ->onDelete('set null');
            $table->string('msg_id', 35);
            $table->dateTime('cre_dt_tm');
            $table->integer('nb_of_txs');
            $table->string('sttlm_mtd', 10)->default('CLRG');
            $table->string('clr_sys_prtry', 10)->nullable();
            $table->string('instg_agt_bic', 11)->nullable();
            $table->string('rtr_id', 35)->nullable();
            $table->string('orgnl_end_to_end_id', 35)->nullable();
            $table->string('orgnl_tx_id', 35)->nullable();
            $table->string('orgnl_tx_ref', 35)->nullable();
            $table->decimal('rtr_intr_bk_sttlm_amt', 18, 3)->nullable();
            $table->string('devise', 3)->default('TND');
            $table->string('chrg_br', 10)->default('SLEV');
            $table->string('dbtr_nm', 140)->nullable();
            $table->string('dbtr_acct_id', 34)->nullable();
            $table->string('dbtr_agt_bic', 11)->nullable();
            $table->string('cdtr_nm', 140)->nullable();
            $table->string('cdtr_acct_id', 34)->nullable();
            $table->string('cdtr_agt_bic', 11)->nullable();
            $table->string('motif_rejet', 50)->nullable();
            $table->string('libelle_rejet', 255)->nullable();
            $table->longText('contenu_xml')->nullable();
            $table->boolean('valide_xsd')->default(false);
            $table->enum('statut', ['GENERE','ENVOYE','ERREUR'])->default('GENERE');
            $table->timestamps();
            $table->index('fichier_id');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tc_pacs004');
    }
};