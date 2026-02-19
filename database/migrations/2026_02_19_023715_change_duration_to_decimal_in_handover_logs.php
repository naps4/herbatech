<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Pastikan tidak ada mode ketat yang mengganggu proses perubahan data
        DB::statement('SET SESSION sql_mode = ""');

        Schema::table('handover_logs', function (Blueprint $table) {
            // nullable() penting jika ada data yang kosong
            // default(0) memastikan tidak ada nilai null setelah migrasi
            $table->decimal('duration_in_hours', 8, 2)->nullable()->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('handover_logs', function (Blueprint $table) {
            //
        });
    }
};
