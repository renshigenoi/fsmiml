<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_photos', function (Blueprint $table) {
            $table->string('stage', 30)->default('completion')->after('uploaded_by');
            $table->index(['work_order_id', 'stage']);
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->text('installation_note')->nullable()->after('completion_note');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('installation_note');
        });

        Schema::table('work_order_photos', function (Blueprint $table) {
            $table->dropIndex(['work_order_id', 'stage']);
            $table->dropColumn('stage');
        });
    }
};

