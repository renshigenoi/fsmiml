<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sesi tracking teknisi lain ditutup dengan alasan "superseded"
        // saat satu teknisi menerima pekerjaan lebih dulu.
        DB::statement('ALTER TABLE tracking_sessions DROP CONSTRAINT tracking_sessions_closed_reason_check');
        DB::statement("ALTER TABLE tracking_sessions ADD CONSTRAINT tracking_sessions_closed_reason_check CHECK (closed_reason IS NULL OR closed_reason IN ('arrived', 'finished', 'cancelled', 'failed', 'superseded'))");

        // Sesi pending yang dibatalkan belum punya started_at, tapi ended_at diisi.
        DB::statement('ALTER TABLE tracking_sessions DROP CONSTRAINT tracking_sessions_time_check');
        DB::statement('ALTER TABLE tracking_sessions ADD CONSTRAINT tracking_sessions_time_check CHECK (ended_at IS NULL OR started_at IS NULL OR ended_at >= started_at)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE tracking_sessions DROP CONSTRAINT tracking_sessions_closed_reason_check');
        DB::statement("ALTER TABLE tracking_sessions ADD CONSTRAINT tracking_sessions_closed_reason_check CHECK (closed_reason IS NULL OR closed_reason IN ('arrived', 'finished', 'cancelled', 'failed'))");

        DB::statement('ALTER TABLE tracking_sessions DROP CONSTRAINT tracking_sessions_time_check');
        DB::statement('ALTER TABLE tracking_sessions ADD CONSTRAINT tracking_sessions_time_check CHECK (ended_at IS NULL OR (started_at IS NOT NULL AND ended_at >= started_at))');
    }
};
