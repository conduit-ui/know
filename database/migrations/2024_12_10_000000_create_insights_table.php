<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insights', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('type')->index();
            $table->json('tags')->nullable();
            $table->string('source')->nullable()->index();
            $table->string('source_id')->nullable();
            $table->string('project')->nullable()->index();
            $table->timestamps();

            // Unique constraint on source + source_id to prevent duplicates
            $table->unique(['source', 'source_id']);

            // Full-text index for search (MySQL/PostgreSQL)
            // Note: SQLite doesn't support fulltext, will use LIKE instead
            if (config('database.default') !== 'sqlite') {
                $table->fullText(['title', 'content']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insights');
    }
};
