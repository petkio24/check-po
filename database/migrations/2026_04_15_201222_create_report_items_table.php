<?php
// database/migrations/2024_01_01_000003_create_report_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->string('program_name');
            $table->string('version');
            $table->string('vendor')->nullable();
            $table->integer('devices_count');
            $table->string('normalized_name');
            $table->string('version_normalized');
            $table->enum('status', ['legitimate', 'illegitimate', 'version_mismatch']);
            $table->foreignId('matched_allowed_id')->nullable()->constrained('allowed_software');
            $table->string('match_type')->nullable(); // 'exact', 'version_mismatch', 'not_found'
            $table->json('match_details')->nullable();
            $table->timestamps();

            $table->index(['normalized_name', 'version_normalized']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_items');
    }
};
