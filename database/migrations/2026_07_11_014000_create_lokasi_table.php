<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lokasi', function (Blueprint $table) {
            $table->increments('id'); // int unsigned, primary key, auto-increment
            $table->string('nama_lokasi', 255);
            $table->char('aktif', 1)->default('Y');
            $table->timestamps(); // created_at, updated_at as timestamp nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi');
    }
};
