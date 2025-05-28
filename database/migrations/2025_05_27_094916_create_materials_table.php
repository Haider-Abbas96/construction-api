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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained("users")->onDelete('cascade');
            $table->foreignId('material_type_id')->nullable()->constrained('material_types')->nullOnDelete();
            $table->string('material_type_name')->nullable();
            $table->string('name');                      // e.g., Cement, Bricks
            $table->string('description')->nullable();   // Optional description
            $table->string('unit');
            $table->decimal('price_per_unit', 10, 2)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('quantity', 10, 2); 
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
