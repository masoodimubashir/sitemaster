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
        Schema::create('attendance_setups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('count')->default(1);
            $table->unsignedBigInteger('price');
            $table->foreignId('site_id')
                ->constrained('sites')
                ->onDelete('cascade')
                ->comment('Site ID to which this attendance setup belongs');
            $table->unsignedBigInteger('setupable_id');
            $table->string('setupable_type');
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_setups');
    }
};
