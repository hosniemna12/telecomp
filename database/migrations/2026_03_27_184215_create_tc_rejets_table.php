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
    Schema::create('tc_rejets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('fichier_id')->constrained('tc_fichiers')->onDelete('cascade');
        $table->foreignId('detail_id')->nullable()->constrained('tc_enr_details')->onDelete('set null');
        $table->string('code_rejet', 10);
        $table->string('motif_rejet', 200)->nullable();
        $table->string('etape_detection', 50);
        $table->boolean('traite')->default(false);
        $table->timestamp('date_traitement')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tc_rejets');
    }
};
