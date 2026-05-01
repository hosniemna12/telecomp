<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration tc_enr_details — Corrigée pour stocker tous les champs
 * de tous les types de valeur SIBTEL (virement, prélèvement, chèques, LDC, papillon).
 *
 * IMPORTANT : montant stocké en TND (decimal 15,3) après division par 1000 au parsing.
 *             NE PAS rediviser dans les services.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tc_enr_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fichier_id')
                  ->constrained('tc_fichiers')
                  ->onDelete('cascade');

            // ── Identification commune ─────────────────────────────
            $table->string('type_valeur', 2)->default('10');
            $table->unsignedInteger('numero_virement')->default(0)
                  ->comment('Numéro virement/chèque/prélèvement/LDC selon type');
            $table->string('code_enregistrement', 2)->nullable()
                  ->comment('21=Présentation, 22=Rejet');

            // ── Montant — TOUJOURS EN TND (après /1000 au parsing) ─
            $table->decimal('montant', 15, 3)->default(0)
                  ->comment('En TND (millimes divisés par 1000 au parsing)');

            // ── RIBs et noms ───────────────────────────────────────
            $table->string('rib_donneur', 20)->nullable()
                  ->comment('RIB donneur ordres / tireur / payeur / tiré selon type');
            $table->string('nom_donneur', 140)->nullable()
                  ->comment('Nom donneur ordres / tiré (Max140Text)');
            $table->string('rib_beneficiaire', 20)->nullable()
                  ->comment('RIB bénéficiaire / créancier / cédant selon type');
            $table->string('nom_beneficiaire', 140)->nullable()
                  ->comment('Nom bénéficiaire / cédant (Max140Text)');

            // ── Institution destinataire ───────────────────────────
            $table->string('code_institution_dest', 2)->nullable();
            $table->string('code_centre_dest', 3)->nullable();

            // ── Virement (type 10) ─────────────────────────────────
            $table->string('reference_dossier', 20)->nullable()
                  ->comment('Réf. dossier paiement — virement');
            $table->string('code_enreg_comp', 1)->nullable()
                  ->comment('Code enregistrement complémentaire/assignation postale');
            $table->unsignedTinyInteger('nb_enreg_comp')->default(0)
                  ->comment('Nombre enregistrements complémentaires');
            $table->string('motif_operation', 45)->nullable()
                  ->comment('Motif opération — virement');
            $table->string('date_compensation', 8)->nullable()
                  ->comment('Date compensation initiale DDMMYYYY');
            $table->string('motif_rejet', 8)->nullable()
                  ->comment('Motif de rejet (code rejets SIBTEL)');
            $table->string('situation_donneur', 1)->nullable()
                  ->comment('0=Résident, 1=Non résident');
            $table->string('type_compte', 1)->nullable()
                  ->comment('1=Dinars, 2=Dinars convertibles, 3=Devises');
            $table->string('nature_compte', 1)->nullable()
                  ->comment('0=Professionnel, 1=Spécial');
            $table->string('existence_dossier', 1)->nullable()
                  ->comment('1=si oui');
            $table->string('zone_libre', 37)->nullable();
            $table->string('code_suivi', 10)->nullable();

            // ── Prélèvement (type 20) ──────────────────────────────
            $table->string('rib_payeur', 20)->nullable();
            $table->string('rib_creancier', 20)->nullable();
            $table->string('code_emetteur', 6)->nullable()
                  ->comment('Code national émetteur prélèvement');
            $table->string('ref_contrat', 20)->nullable()
                  ->comment('Référence contrat de domiciliation');
            $table->string('libelle_prelevement', 50)->nullable();
            $table->string('date_echeance', 8)->nullable()
                  ->comment('Date échéance DDMMYYYY');
            $table->string('code_payeur', 8)->nullable();
            $table->string('code_maj', 1)->nullable();
            $table->string('date_maj', 8)->nullable();

            // ── Chèques (types 30, 31, 32, 33, 82, 83) ────────────
            $table->string('rib_tireur', 20)->nullable();
            $table->string('numero_cheque', 7)->nullable();
            $table->string('date_emission', 8)->nullable()
                  ->comment('Date émission chèque DDMMYYYY');
            $table->string('lieu_emission', 1)->nullable()
                  ->comment('Code lieu émission');
            $table->string('situation_beneficiaire', 1)->nullable();
            $table->string('date_cnp', 8)->nullable()
                  ->comment('Date établissement CNP DDMMYYYY');
            $table->string('numero_cnp', 4)->nullable();
            $table->string('code_devise_position', 3)->nullable();
            $table->decimal('montant_reclame', 15, 3)->nullable()
                  ->comment('Montant réclamé CNP/ARP en TND');
            $table->decimal('montant_provision', 15, 3)->nullable()
                  ->comment('Montant de la provision en TND');
            $table->decimal('montant_regularise', 15, 3)->nullable()
                  ->comment('Montant régularisé ARP en TND');
            $table->string('date_preaviss', 8)->nullable();
            $table->string('date_presentation', 8)->nullable();
            $table->string('date_delivrance', 8)->nullable();
            $table->string('signature_electronique', 128)->nullable();
            $table->string('ref_cle_publique', 14)->nullable();
            // Images chèques encodées base64
            $table->text('img_recto')->nullable()
                  ->comment('Image recto chèque encodée base64');
            $table->text('img_verso')->nullable()
                  ->comment('Image verso chèque encodée base64');
            // Champs doc joint
            $table->string('date_doc_joint', 8)->nullable();
            $table->string('numero_doc_joint', 20)->nullable();
            $table->string('code_valeur_doc_joint', 1)->nullable();
            $table->string('motif_rejet_doc_joint', 8)->nullable();

            // ── Papillon (type 84) ─────────────────────────────────
            $table->string('date_etablissement', 8)->nullable()
                  ->comment('Date établissement papillon DDMMYYYY');
            $table->string('numero_papillon', 4)->nullable();

            // ── Lettres de change (types 40-43) ───────────────────
            $table->string('numero_lettre_change', 12)->nullable();
            $table->string('rib_tire', 20)->nullable();
            $table->string('rib_tire_initial', 20)->nullable();
            $table->string('rib_cedant', 20)->nullable();
            $table->string('nom_cedant', 30)->nullable();
            $table->string('nom_tire', 30)->nullable();
            $table->string('ref_commerciales_tire', 30)->nullable();
            $table->string('ref_commerciales_tireur', 30)->nullable();
            $table->decimal('montant_interets', 15, 3)->nullable()
                  ->comment('Montant intérêts LDC en TND');
            $table->decimal('montant_frais_protest', 15, 3)->nullable()
                  ->comment('Montant frais protêt LDC en TND');
            $table->string('code_acceptation', 1)->nullable();
            $table->string('code_endossement', 1)->nullable();
            $table->string('date_echeance_ldc', 8)->nullable()
                  ->comment('Date échéance LDC DDMMYYYY');
            $table->string('date_echeance_initiale', 8)->nullable();
            $table->string('date_creation', 8)->nullable()
                  ->comment('Date création LDC DDMMYYYY');
            $table->string('lieu_creation', 30)->nullable();
            $table->string('code_ordre_payer', 1)->nullable();
            $table->string('situation_cedant', 1)->nullable();
            $table->string('code_risque_bct', 6)->nullable();
            $table->text('messages')->nullable()
                  ->comment('Messages complémentaires');

            // ── Statut du détail ───────────────────────────────────
            $table->string('code_rejet', 10)->nullable()
                  ->comment('Code rejet détecté lors de la validation');
            $table->string('statut', 20)->default('EN_ATTENTE')
                  ->comment('EN_ATTENTE, TRAITE, ERREUR, REJET');

            $table->timestamps();

            // ── Index pour les recherches fréquentes ──────────────
            $table->index('fichier_id');
            $table->index('type_valeur');
            $table->index('statut');
            $table->index('rib_donneur');
            $table->index('rib_beneficiaire');
            $table->index('numero_virement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tc_enr_details');
    }
};