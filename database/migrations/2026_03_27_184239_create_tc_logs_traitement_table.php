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
    Schema::create('tc_logs_traitement', function (Blueprint $table) {
        $table->id();
        $table->foreignId('fichier_id')->constrained('tc_fichiers')->onDelete('cascade');
        $table->string('etape', 50);
        $table->string('niveau', 10);
        $table->text('message');
        $table->json('donnees_contexte')->nullable();
        $table->timestamp('created_at')->useCurrent();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tc_logs_traitement');
    }
};
