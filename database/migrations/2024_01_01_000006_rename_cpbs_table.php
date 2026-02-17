<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Jika table c_p_b_s sudah ada, rename ke cpbs
        if (Schema::hasTable('c_p_b_s') && !Schema::hasTable('cpbs')) {
            Schema::rename('c_p_b_s', 'cpbs');
        }
    }

    public function down()
    {
        if (Schema::hasTable('cpbs') && !Schema::hasTable('c_p_b_s')) {
            Schema::rename('cpbs', 'c_p_b_s');
        }
    }
};