<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filter_presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('resource_class');
            $table->json('filters');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Ensure unique filter names per user per resource
            $table->unique(['user_id', 'resource_class', 'name'], 'unique_user_resource_name');

            // Index for performance
            $table->index(['user_id', 'resource_class']);
            $table->index(['user_id', 'resource_class', 'is_default'], 'user_resource_default_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filter_presets');
    }
};
