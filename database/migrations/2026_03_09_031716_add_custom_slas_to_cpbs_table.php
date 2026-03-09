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
        Schema::table('cpbs', function (Blueprint $table) {
            // Menambahkan kolom JSON untuk menyimpan settingan jam tiap departemen
            $table->json('custom_slas')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cpbs', function (Blueprint $table) {
            $table->dropColumn('custom_slas');
        });
    }
};