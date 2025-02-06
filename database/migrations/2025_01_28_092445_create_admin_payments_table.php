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
        Schema::create('admin_payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('entity');
            $table->decimal('amount', 10, 2);
            $table->boolean('transaction_type')->comment('1 => Sent, 0 => Received');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_payments');
    }
};
