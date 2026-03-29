<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_part_id')->constrained('parts')->cascadeOnDelete();
            $table->foreignId('child_part_id')->constrained('parts')->cascadeOnDelete();
            $table->decimal('quantity', 10, 4)->default(1);
            $table->string('unit_of_measure')->default('each');
            $table->decimal('scrap_percentage', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_test')->default(false);
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['parent_part_id', 'child_part_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_of_materials');
    }
};
