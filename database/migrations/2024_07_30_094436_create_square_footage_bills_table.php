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
        Schema::create('square_footage_bills', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->string('wager_name');
            $table->decimal('price');
            $table->enum('type', ['per_sqr_ft', 'per_unit', 'full_contract']);
            $table->decimal('multiplier', 10, 2);
            $table->foreignId('phase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->boolean('verified_by_admin')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('square_footage_bills');
    }
};
