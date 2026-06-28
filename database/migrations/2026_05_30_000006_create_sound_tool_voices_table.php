<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sound_tool_voices', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->default('elevenlabs');
            $table->string('provider_voice_id')->unique();
            $table->string('name');
            $table->string('language_code', 20)->nullable();
            $table->string('description', 500)->nullable();
            $table->string('category', 50)->default('cloned');
            $table->string('sample_audio_path')->nullable();
            $table->string('preview_url')->nullable();
            $table->json('labels')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sound_tool_voices');
    }
};
