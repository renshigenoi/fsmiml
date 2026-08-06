<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->string('window_film_desc', 255)->nullable()->after('product_name');
        });

        // Backfill data lama dari source_payload sales order (kolom window_film_desc di DB legacy).
        DB::statement(
            'UPDATE work_order_items AS woi
             SET window_film_desc = so.source_payload->>\'window_film_desc\'
             FROM work_orders AS wo
             JOIN sales_orders AS so ON so.id = wo.sales_order_id
             WHERE woi.work_order_id = wo.id
               AND woi.window_film_desc IS NULL
               AND so.source_payload IS NOT NULL'
        );
    }

    public function down(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->dropColumn('window_film_desc');
        });
    }
};
