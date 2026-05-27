<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_dispatches', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 24)->unique();

            $table->date('dispatch_date');
            $table->time('dispatch_time')->nullable();

            $table->string('sample_id', 100);
            $table->string('sample_type', 50)->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);

            $table->string('destination', 100);

            // Driver — either a registered Administration user or an ad-hoc entry
            $table->foreignId('driver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('driver_name', 150);
            $table->string('driver_phone', 30)->nullable();
            $table->string('vehicle_registration', 20)->nullable();

            $table->foreignId('dispatched_by_user_id')->constrained('users');

            $table->enum('status', ['dispatched', 'received', 'processed'])->default('dispatched');
            $table->text('notes')->nullable();

            // Receipt fields — filled when lab confirms arrival
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('condition_on_arrival', 20)->nullable();
            $table->text('rejection_reason')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_dispatches');
    }
};
