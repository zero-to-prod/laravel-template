<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', static function (Blueprint $Blueprint) {
            $Blueprint->id()->comment('The unique identifier of the queued job');
            $Blueprint->string('queue')->index()->comment('The queue the job was pushed onto');
            $Blueprint->longText('payload')->comment('The serialized job');
            $Blueprint->unsignedTinyInteger('attempts')->comment('How many times the job has been attempted');
            $Blueprint->unsignedInteger('reserved_at')->nullable()->comment('The unix timestamp a worker reserved the job');
            $Blueprint->unsignedInteger('available_at')->comment('The unix timestamp the job becomes available to run');
            $Blueprint->unsignedInteger('created_at')->comment('The unix timestamp the job was pushed');
        });

        Schema::create('job_batches', static function (Blueprint $Blueprint) {
            $Blueprint->string('id')->primary()->comment('The unique identifier of the batch');
            $Blueprint->string('name')->comment('The name of the batch');
            $Blueprint->integer('total_jobs')->comment('How many jobs the batch started with');
            $Blueprint->integer('pending_jobs')->comment('How many jobs in the batch have yet to finish');
            $Blueprint->integer('failed_jobs')->comment('How many jobs in the batch failed');
            $Blueprint->longText('failed_job_ids')->comment('The identifiers of the jobs that failed');
            $Blueprint->mediumText('options')->nullable()->comment('The serialized batch options');
            $Blueprint->integer('cancelled_at')->nullable()->comment('The unix timestamp the batch was cancelled');
            $Blueprint->integer('created_at')->comment('The unix timestamp the batch was created');
            $Blueprint->integer('finished_at')->nullable()->comment('The unix timestamp the batch finished');
        });

        Schema::create('failed_jobs', static function (Blueprint $Blueprint) {
            $Blueprint->id()->comment('The unique identifier of the failure');
            $Blueprint->string('uuid')->unique()->comment('The unique identifier of the job that failed');
            $Blueprint->text('connection')->comment('The queue connection the job ran on');
            $Blueprint->text('queue')->comment('The queue the job ran on');
            $Blueprint->longText('payload')->comment('The serialized job');
            $Blueprint->longText('exception')->comment('The exception that failed the job');
            $Blueprint->timestamp('failed_at')->useCurrent()->comment('When the job failed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
