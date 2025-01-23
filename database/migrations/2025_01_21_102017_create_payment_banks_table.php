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
        Schema::create('payment_banks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from');
            $table->string('from_type');
            $table->unsignedBigInteger('to');
            $table->string('to_type');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_on_going')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_banks');
    }
};
