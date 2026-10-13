<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('page_number')->nullable();
            $table->string('note_type')->default('note');
            $table->string('title')->nullable();
            $table->longText('content');
            $table->timestamps();

            $table->index(['book_id', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_notes');
    }
};
