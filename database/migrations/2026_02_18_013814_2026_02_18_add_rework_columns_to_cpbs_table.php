<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cpbs', function (Blueprint $table) {
            $table->boolean('is_rework')->default(false)->after('is_overdue');
            $table->text('rework_note')->nullable()->after('is_rework');
        });
    }

    public function down(): void
    {
        Schema::table('cpbs', function (Blueprint $table) {
            $table->dropColumn(['is_rework', 'rework_note']);
        });
    }
};  
 