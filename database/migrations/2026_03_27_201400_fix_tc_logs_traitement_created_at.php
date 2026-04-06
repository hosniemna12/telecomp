<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('tc_logs_traitement', function (Blueprint $table) {
        $table->dropColumn('created_at');
    });

    Schema::table('tc_logs_traitement', function (Blueprint $table) {
        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('tc_logs_traitement', function (Blueprint $table) {
        $table->dropColumn('created_at');
    });
}
};
