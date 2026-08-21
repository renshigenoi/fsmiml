<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('leave_requests')->where('type', 'permission')->whereNull('leave_end_date')
            ->update(['leave_end_date' => DB::raw('leave_date')]);
    }

    public function down(): void {}
};
