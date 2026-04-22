<?php
// database/migrations/2024_01_01_000001_create_allowed_software_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('allowed_software', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('version')->nullable();
            $table->string('vendor')->nullable();
            $table->string('normalized_name')->index();
            $table->string('version_normalized')->nullable();
            $table->json('version_parts')->nullable(); // [major, minor, patch, build]
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['normalized_name', 'version_normalized', 'vendor']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('allowed_software');
    }
};
