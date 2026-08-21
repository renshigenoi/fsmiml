<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_photos', function (Blueprint $table) {
            $table->timestampTz('captured_at')->nullable()->after('size_bytes');
            $table->string('sync_token', 100)->nullable()->after('captured_at');
            $table->index('sync_token');
        });
    }

    public function down(): void
    {
        Schema::table('work_order_photos', function (Blueprint $table) {
            $table->dropIndex(['sync_token']);
            $table->dropColumn(['captured_at', 'sync_token']);
        });
    }
};
