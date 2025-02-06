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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('screenshot')->nullable();
            $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('amount', 8, 2);
            $table->boolean('verified_by_admin')->default(false);
            $table->boolean('transaction_type')->comment('1 => Sent , 0 => Received');
            $table->boolean('payment_initiator')->comment('If Site Or Supplier Exits => 1, Admin => 0');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
