<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracking_sessions', function (Blueprint $table) {
            $table->string('realtime_channel', 64)->nullable()->unique()->after('closed_reason');
        });
    }

    public function down(): void
    {
        Schema::table('tracking_sessions', function (Blueprint $table) {
            $table->dropUnique(['realtime_channel']);
            $table->dropColumn('realtime_channel');
        });
    }
};
