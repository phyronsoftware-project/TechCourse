<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tech_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('tech_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('title')->nullable();
            $table->longText('detail')->nullable();
            $table->string('website_url', 500)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tech_details');
    }
};
