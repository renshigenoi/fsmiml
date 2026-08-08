<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX work_orders_status_created_at_index ON work_orders (status, created_at)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS work_orders_status_created_at_index');
    }
};
