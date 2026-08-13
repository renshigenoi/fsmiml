<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('radius_meters')->default(150);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::table('technicians', function (Blueprint $table): void {
            $table->foreignId('work_location_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('attendance_mode', 30)->default('anywhere')->after('is_active');
            $table->unsignedInteger('attendance_radius_override')->nullable()->after('attendance_mode');
        });

        Schema::create('attendance_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->date('attendance_date');
            $table->timestampTz('check_in_at')->nullable();
            $table->string('check_in_photo_path')->nullable();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->decimal('check_in_accuracy_meters', 8, 2)->nullable();
            $table->unsignedInteger('check_in_distance_meters')->nullable();
            $table->string('check_in_location_status', 30)->nullable();
            $table->timestampTz('check_out_at')->nullable();
            $table->string('check_out_photo_path')->nullable();
            $table->decimal('check_out_latitude', 10, 7)->nullable();
            $table->decimal('check_out_longitude', 10, 7)->nullable();
            $table->decimal('check_out_accuracy_meters', 8, 2)->nullable();
            $table->unsignedInteger('check_out_distance_meters')->nullable();
            $table->string('check_out_location_status', 30)->nullable();
            $table->timestampsTz();
            $table->unique(['user_id', 'attendance_date']);
        });

        Schema::create('leave_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->date('leave_date');
            $table->text('note')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestampsTz();
            $table->index(['user_id', 'leave_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendance_records');
        Schema::table('technicians', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('work_location_id');
            $table->dropColumn(['attendance_mode', 'attendance_radius_override']);
        });
        Schema::dropIfExists('work_locations');
    }
};
