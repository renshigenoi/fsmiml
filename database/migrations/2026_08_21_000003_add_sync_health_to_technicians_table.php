<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->unsignedSmallInteger('offline_sync_pending_count')->default(0);
            $table->timestampTz('offline_sync_last_reported_at')->nullable();
            $table->index(['offline_sync_pending_count', 'offline_sync_last_reported_at']);
        });
    }

    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->dropIndex(['offline_sync_pending_count', 'offline_sync_last_reported_at']);
            $table->dropColumn(['offline_sync_pending_count', 'offline_sync_last_reported_at']);
        });
    }
};
