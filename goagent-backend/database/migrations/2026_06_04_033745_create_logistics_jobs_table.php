<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logistics_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('importer_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('origin_port');
            $table->string('destination');
            $table->decimal('budget', 15, 2);
            $table->date('expected_delivery_date');
            $table->enum('status', ['open', 'awarded', 'in_progress', 'completed'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistics_jobs');
    }
};
