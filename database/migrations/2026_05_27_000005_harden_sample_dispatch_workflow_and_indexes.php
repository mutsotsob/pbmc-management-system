<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sample_dispatches MODIFY COLUMN status ENUM('dispatched','received','processed') NOT NULL DEFAULT 'dispatched'");
        }

        Schema::table('sample_dispatches', function (Blueprint $table) {
            $table->index('status', 'sample_dispatches_status_idx');
            $table->index('study', 'sample_dispatches_study_idx');
            $table->index('dispatch_date', 'sample_dispatches_dispatch_date_idx');
            $table->index('received_at', 'sample_dispatches_received_at_idx');
            $table->index(['status', 'dispatch_date'], 'sample_dispatches_status_dispatch_date_idx');
        });

        Schema::table('sample_dispatch_items', function (Blueprint $table) {
            $table->unique(['sample_dispatch_id', 'participant_id'], 'sample_dispatch_items_dispatch_participant_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sample_dispatch_items', function (Blueprint $table) {
            $table->dropUnique('sample_dispatch_items_dispatch_participant_unique');
        });

        Schema::table('sample_dispatches', function (Blueprint $table) {
            $table->dropIndex('sample_dispatches_status_idx');
            $table->dropIndex('sample_dispatches_study_idx');
            $table->dropIndex('sample_dispatches_dispatch_date_idx');
            $table->dropIndex('sample_dispatches_received_at_idx');
            $table->dropIndex('sample_dispatches_status_dispatch_date_idx');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sample_dispatches MODIFY COLUMN status ENUM('dispatched','received') NOT NULL DEFAULT 'dispatched'");
        }
    }
};
