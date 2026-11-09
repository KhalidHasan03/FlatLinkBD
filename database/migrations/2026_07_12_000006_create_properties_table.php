<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->foreignId('property_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('purpose', ['sale', 'rental'])->default('sale');
            $table->decimal('price', 12, 2);
            $table->boolean('price_negotiable')->default(true);
            $table->decimal('size_sqft', 10, 2);
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            $table->integer('floor_no')->nullable();
            $table->integer('total_floors')->nullable();
            $table->enum('condition', ['under_construction', 'new', 'renovated', 'old']);
            $table->text('address');
            $table->foreignId('area_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('video_url')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'published'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->integer('views_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
