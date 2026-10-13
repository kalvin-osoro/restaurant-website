<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('practice_areas', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name')->unique();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });
        Schema::create('attorneys', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->enum('role', ['Managing Partner', 'Senior Partner', 'Partner', 'Of Counsel']);
            $table->text('bio');
            $table->text('image_url');
            $table->string('email');
            $table->string('phone');
            $table->json('education');
            $table->json('admissions');
            $table->json('notable_cases');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
            $table->index(['status', 'display_order']);
        });
        Schema::create('attorney_practice_area', function (Blueprint $table) {
            $table->foreignId('attorney_id')->constrained()->cascadeOnDelete();
            $table->foreignId('practice_area_id')->constrained()->cascadeOnDelete();
            $table->primary(['attorney_id', 'practice_area_id']);
        });
        Schema::create('case_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name')->unique();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });
        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('case_category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('value')->nullable();
            $table->string('timeline')->nullable();
            $table->string('jurisdiction')->nullable();
            $table->text('outcome_summary');
            $table->json('key_details');
            $table->text('image_url')->nullable();
            $table->string('client_industry');
            $table->boolean('confidential')->default(true);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->unsignedInteger('display_order')->default(0);
            $table->unsignedInteger('featured_position')->nullable();
            $table->timestamps();
            $table->index(['status', 'confidential', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_studies');
        Schema::dropIfExists('case_categories');
        Schema::dropIfExists('attorney_practice_area');
        Schema::dropIfExists('attorneys');
        Schema::dropIfExists('practice_areas');
    }
};
