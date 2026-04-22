<?php
// database/migrations/2024_01_01_000005_create_pc_check_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pc_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pc_check_id')->constrained()->onDelete('cascade');
            $table->string('program_name');
            $table->string('version');
            $table->string('vendor')->nullable();
            $table->string('normalized_name');
            $table->string('version_normalized');
            $table->enum('status', ['legitimate', 'illegitimate', 'version_mismatch']);
            $table->foreignId('matched_allowed_id')->nullable()->constrained('allowed_software');
            $table->text('match_details')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pc_check_items');
    }
};
