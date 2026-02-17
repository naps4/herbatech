<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('handover_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpb_id')->constrained()->onDelete('cascade');
            
            // Status transition
            $table->string('from_status');
            $table->string('to_status');
            
            // Users involved
            $table->foreignId('handed_by')->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            
            // Timestamps
            $table->timestamp('handed_at')->useCurrent();
            $table->timestamp('received_at')->nullable();
            
            // Duration calculation
            $table->integer('duration_in_hours')->nullable()->comment('Duration at previous location');
            $table->boolean('was_overdue')->default(false);
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['cpb_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('handover_logs');
    }
};