<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 100)->unique();
            $table->string('invoice_number', 100)->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('status', 50);
            $table->timestampTz('ordered_at')->nullable();
            $table->jsonb('source_payload')->nullable();
            $table->timestampTz('synced_at');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
