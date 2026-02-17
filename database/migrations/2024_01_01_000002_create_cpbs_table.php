<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cpbs', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->enum('type', ['pengolahan', 'pengemasan']);
            $table->string('product_name');
            $table->integer('schedule_duration')->comment('Duration in hours');
            $table->enum('status', [
                'rnd',
                'qa',
                'ppic',
                'wh',
                'produksi',
                'qc',
                'qa_final',
                'released'
            ])->default('rnd');
            
            // Time tracking
            $table->timestamp('entered_current_status_at')->useCurrent();
            $table->integer('duration_in_current_status')->default(0);
            $table->boolean('is_overdue')->default(false);
            $table->timestamp('overdue_since')->nullable();
            
            // Foreign keys
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('current_department_id')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cpbs');
    }
};