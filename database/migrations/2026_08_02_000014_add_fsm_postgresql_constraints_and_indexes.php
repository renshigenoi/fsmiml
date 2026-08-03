<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('administrator', 'coordinator', 'technician'))");

        DB::statement('ALTER TABLE service_locations ADD CONSTRAINT service_locations_coordinates_check CHECK ((latitude IS NULL AND longitude IS NULL) OR (latitude IS NOT NULL AND longitude IS NOT NULL))');
        DB::statement('ALTER TABLE service_locations ADD CONSTRAINT service_locations_latitude_range_check CHECK (latitude IS NULL OR latitude BETWEEN -90 AND 90)');
        DB::statement('ALTER TABLE service_locations ADD CONSTRAINT service_locations_longitude_range_check CHECK (longitude IS NULL OR longitude BETWEEN -180 AND 180)');

        DB::statement('ALTER TABLE sales_order_items ADD CONSTRAINT sales_order_items_quantity_positive_check CHECK (quantity > 0)');

        DB::statement("ALTER TABLE work_orders ADD CONSTRAINT work_orders_status_check CHECK (status IN ('draft', 'waiting_acceptance', 'accepted', 'on_the_way', 'arrived', 'installation', 'finished', 'rejected', 'cancelled', 'failed'))");
        DB::statement('ALTER TABLE work_orders ADD CONSTRAINT work_orders_schedule_check CHECK (scheduled_end_at IS NULL OR scheduled_end_at > scheduled_start_at)');
        DB::statement("ALTER TABLE work_orders ADD CONSTRAINT work_orders_cancelled_reason_check CHECK (status <> 'cancelled' OR (cancelled_reason IS NOT NULL AND btrim(cancelled_reason) <> ''))");
        DB::statement("ALTER TABLE work_orders ADD CONSTRAINT work_orders_failed_reason_check CHECK (status <> 'failed' OR (failed_reason IS NOT NULL AND btrim(failed_reason) <> ''))");

        DB::statement('ALTER TABLE work_order_items ADD CONSTRAINT work_order_items_quantity_positive_check CHECK (quantity > 0)');

        DB::statement("ALTER TABLE assignments ADD CONSTRAINT assignments_status_check CHECK (status IN ('pending', 'accepted', 'rejected', 'superseded', 'cancelled'))");
        DB::statement("ALTER TABLE assignments ADD CONSTRAINT assignments_rejected_reason_check CHECK (status <> 'rejected' OR (rejected_reason IS NOT NULL AND btrim(rejected_reason) <> ''))");

        DB::statement("ALTER TABLE work_order_status_histories ADD CONSTRAINT work_order_status_histories_from_status_check CHECK (from_status IS NULL OR from_status IN ('draft', 'waiting_acceptance', 'accepted', 'on_the_way', 'arrived', 'installation', 'finished', 'rejected', 'cancelled', 'failed'))");
        DB::statement("ALTER TABLE work_order_status_histories ADD CONSTRAINT work_order_status_histories_to_status_check CHECK (to_status IN ('draft', 'waiting_acceptance', 'accepted', 'on_the_way', 'arrived', 'installation', 'finished', 'rejected', 'cancelled', 'failed'))");
        DB::statement("ALTER TABLE work_order_status_histories ADD CONSTRAINT work_order_status_histories_reason_check CHECK (to_status NOT IN ('rejected', 'cancelled', 'failed') OR (reason IS NOT NULL AND btrim(reason) <> ''))");

        DB::statement("ALTER TABLE tracking_sessions ADD CONSTRAINT tracking_sessions_status_check CHECK (status IN ('pending', 'active', 'closed', 'cancelled'))");
        DB::statement("ALTER TABLE tracking_sessions ADD CONSTRAINT tracking_sessions_closed_reason_check CHECK (closed_reason IS NULL OR closed_reason IN ('arrived', 'finished', 'cancelled', 'failed'))");
        DB::statement('ALTER TABLE tracking_sessions ADD CONSTRAINT tracking_sessions_time_check CHECK (ended_at IS NULL OR (started_at IS NOT NULL AND ended_at >= started_at))');

        DB::statement('ALTER TABLE tracking_points ADD CONSTRAINT tracking_points_latitude_check CHECK (latitude BETWEEN -90 AND 90)');
        DB::statement('ALTER TABLE tracking_points ADD CONSTRAINT tracking_points_longitude_check CHECK (longitude BETWEEN -180 AND 180)');
        DB::statement('ALTER TABLE tracking_points ADD CONSTRAINT tracking_points_accuracy_check CHECK (accuracy_meters IS NULL OR accuracy_meters >= 0)');
        DB::statement('ALTER TABLE tracking_points ADD CONSTRAINT tracking_points_speed_check CHECK (speed_mps IS NULL OR speed_mps >= 0)');
        DB::statement('ALTER TABLE tracking_points ADD CONSTRAINT tracking_points_heading_check CHECK (heading_degrees IS NULL OR (heading_degrees >= 0 AND heading_degrees < 360))');

        DB::statement("ALTER TABLE tracking_tokens ADD CONSTRAINT tracking_tokens_status_check CHECK (status IN ('active', 'revoked', 'expired'))");
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_channel_check CHECK (channel IN ('push', 'whatsapp', 'email'))");
        DB::statement("ALTER TABLE notifications ADD CONSTRAINT notifications_status_check CHECK (status IN ('queued', 'sent', 'failed'))");

        DB::statement('CREATE INDEX work_orders_status_scheduled_start_at_index ON work_orders (status, scheduled_start_at)');
        DB::statement('CREATE INDEX work_orders_customer_scheduled_start_at_index ON work_orders (customer_id, scheduled_start_at)');
        DB::statement('CREATE INDEX assignments_technician_status_index ON assignments (technician_id, status)');
        DB::statement('CREATE INDEX assignments_work_order_assigned_at_index ON assignments (work_order_id, assigned_at DESC)');
        DB::statement('CREATE INDEX work_order_status_histories_work_order_occurred_at_index ON work_order_status_histories (work_order_id, occurred_at DESC)');
        DB::statement('CREATE INDEX tracking_sessions_work_order_status_index ON tracking_sessions (work_order_id, status)');
        DB::statement('CREATE INDEX tracking_points_session_recorded_at_index ON tracking_points (tracking_session_id, recorded_at DESC)');
        DB::statement('CREATE INDEX notifications_user_status_created_at_index ON notifications (user_id, status, created_at DESC)');
        DB::statement("CREATE UNIQUE INDEX tracking_sessions_one_active_per_assignment ON tracking_sessions (assignment_id) WHERE status = 'active'");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS tracking_sessions_one_active_per_assignment');
        DB::statement('DROP INDEX IF EXISTS notifications_user_status_created_at_index');
        DB::statement('DROP INDEX IF EXISTS tracking_points_session_recorded_at_index');
        DB::statement('DROP INDEX IF EXISTS tracking_sessions_work_order_status_index');
        DB::statement('DROP INDEX IF EXISTS work_order_status_histories_work_order_occurred_at_index');
        DB::statement('DROP INDEX IF EXISTS assignments_work_order_assigned_at_index');
        DB::statement('DROP INDEX IF EXISTS assignments_technician_status_index');
        DB::statement('DROP INDEX IF EXISTS work_orders_customer_scheduled_start_at_index');
        DB::statement('DROP INDEX IF EXISTS work_orders_status_scheduled_start_at_index');
    }
};
