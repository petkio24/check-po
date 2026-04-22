<?php
// database/migrations/2024_01_01_000004_create_pc_checks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pc_checks', function (Blueprint $table) {
            $table->id();
            $table->string('check_name');
            $table->string('pc_name')->nullable();
            $table->string('pc_ip')->nullable();
            $table->string('check_file_name')->nullable();
            $table->integer('total_software')->default(0);
            $table->integer('legitimate_count')->default(0);
            $table->integer('illegitimate_count')->default(0);
            $table->integer('version_mismatch_count')->default(0);
            $table->json('results')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pc_checks');
    }
};
