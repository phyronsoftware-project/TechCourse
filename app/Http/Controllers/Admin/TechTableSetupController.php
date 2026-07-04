<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TechTableSetupController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        Artisan::call('migrate', ['--force' => true]);

        if (! Schema::hasTable('tech_categories')) {
            Schema::create('tech_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('subtitle')->nullable();
                $table->string('icon', 120)->nullable()->default('fa-solid fa-microchip');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index('status');
                $table->index('sort_order');
            });
        }

        if (! Schema::hasTable('tech_details')) {
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

        return redirect()
            ->route('admin.tech-categories.index')
            ->with('success', 'Tech tables setup completed on this server.');
    }
}
