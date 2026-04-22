<?php
// database/migrations/2024_01_01_000002_create_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->integer('total_entries');
            $table->integer('legitimate_count')->default(0);
            $table->integer('illegitimate_count')->default(0);
            $table->integer('version_mismatch_count')->default(0);
            $table->json('summary')->nullable();
            $table->json('filters_used')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
