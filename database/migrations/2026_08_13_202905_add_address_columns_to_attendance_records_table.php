<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            // Menambahkan kolom alamat setelah koordinat GPS masing-masing
            $table->text('check_in_address')->nullable()->after('check_in_longitude');
            $table->text('check_out_address')->nullable()->after('check_out_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn(['check_in_address', 'check_out_address']);
        });
    }
};