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
        Schema::create('wagers', function (Blueprint $table) {
            $table->id();
            $table->string('wager_name');
            $table->unsignedBigInteger('price')->default(0);
            $table->foreignId('wasta_id')->nullable()
                ->constrained('wastas')
                ->onDelete('cascade')
                ->comment('Attached Wasta Id, if any ie: any worker that is working under wasta');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wagers');
    }
};
