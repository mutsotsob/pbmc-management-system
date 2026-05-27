<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_dispatch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_dispatch_id')->constrained('sample_dispatches')->cascadeOnDelete();
            $table->string('participant_id', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_dispatch_items');
    }
};
