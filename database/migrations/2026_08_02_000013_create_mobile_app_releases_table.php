<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_app_releases', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 20);
            $table->integer('version_code');
            $table->string('version_name', 30);
            $table->integer('minimum_version_code');
            $table->boolean('is_mandatory')->default(false);
            $table->string('apk_url', 2048);
            $table->char('sha256', 64);
            $table->text('release_notes')->nullable();
            $table->timestampTz('published_at')->nullable();
            $table->timestampsTz();
            $table->unique(['platform', 'version_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_app_releases');
    }
};
