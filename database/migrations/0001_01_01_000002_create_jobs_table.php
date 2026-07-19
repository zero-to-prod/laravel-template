<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', static function (Blueprint $Blueprint) {
            $Blueprint->id();
            $Blueprint->string('queue')->index();
            $Blueprint->longText('payload');
            $Blueprint->unsignedTinyInteger('attempts');
            $Blueprint->unsignedInteger('reserved_at')->nullable();
            $Blueprint->unsignedInteger('available_at');
            $Blueprint->unsignedInteger('created_at');
        });

        Schema::create('job_batches', static function (Blueprint $Blueprint) {
            $Blueprint->string('id')->primary();
            $Blueprint->string('name');
            $Blueprint->integer('total_jobs');
            $Blueprint->integer('pending_jobs');
            $Blueprint->integer('failed_jobs');
            $Blueprint->longText('failed_job_ids');
            $Blueprint->mediumText('options')->nullable();
            $Blueprint->integer('cancelled_at')->nullable();
            $Blueprint->integer('created_at');
            $Blueprint->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', static function (Blueprint $Blueprint) {
            $Blueprint->id();
            $Blueprint->string('uuid')->unique();
            $Blueprint->text('connection');
            $Blueprint->text('queue');
            $Blueprint->longText('payload');
            $Blueprint->longText('exception');
            $Blueprint->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
