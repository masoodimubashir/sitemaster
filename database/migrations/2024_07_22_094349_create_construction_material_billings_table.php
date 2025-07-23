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
        Schema::create('construction_material_billings', function (Blueprint $table) {
            $table->id();
            $table->string('item_image_path')->nullable();
            $table->string('item_name');
            $table->decimal('amount', 12, 2)->nullable()->default(0.00);
            $table->integer('unit_count')->default(1);
            $table->boolean('verified_by_admin')->default(false);
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('phase_id')->constrained()->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('construction_material_billings');
    }
};
