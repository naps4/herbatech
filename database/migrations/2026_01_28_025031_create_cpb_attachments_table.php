<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cpb_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpb_id')->constrained('cpbs')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('file_path');
            $table->string('file_name'); // Original name
            $table->string('file_type')->nullable(); // Extension or mime
            $table->string('description')->nullable(); // Note for the file
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cpb_attachments');
    }
};
