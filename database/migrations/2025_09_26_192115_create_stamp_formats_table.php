<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stamp_formats', function (Blueprint $table) {
    $table->id();
    $table->string('prefix', 10);
    $table->string('suffix', 10);
    $table->boolean('is_active')->default(1);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stamp_formats');
    }
};
