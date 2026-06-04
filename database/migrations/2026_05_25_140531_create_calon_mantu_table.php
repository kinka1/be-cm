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
        Schema::create('calon_mantu', function (Blueprint $table) {
            $table->id();
            $table->string('table_number');
            $table->string('qr_code')->unique();
            $table->unsignedInteger('capacity')->default(1);
            $table->enum('status', ['available', 'occupied', 'reserved'])->default('available');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calon_mantu');
    }
};
