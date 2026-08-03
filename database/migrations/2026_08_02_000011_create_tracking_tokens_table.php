<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracking_session_id')->constrained()->restrictOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->string('status', 20)->default('active');
            $table->timestampTz('expires_at');
            $table->timestampTz('revoked_at')->nullable();
            $table->timestampTz('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_tokens');
    }
};
