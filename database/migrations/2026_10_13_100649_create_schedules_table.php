<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('todo_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('timezone')->default('UTC');
            $table->string('location')->nullable();
            $table->string('google_event_id')->nullable();
            $table->string('sync_status')->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'start_at']);
            $table->index(['user_id', 'todo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
