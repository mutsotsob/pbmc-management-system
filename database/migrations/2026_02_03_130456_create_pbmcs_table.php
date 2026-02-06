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
        Schema::create('pbmcs', function (Blueprint $table) {
            $table->id();

            // --------------------
            // Study Information
            // --------------------
            $table->string('study_choice');
            $table->string('other_study_name')->nullable();

            // --------------------
            // PT Details
            // --------------------
            $table->string('ptid');
            $table->string('visit');
            $table->date('collection_date');
            $table->time('collection_time');
            $table->date('process_start_date')->nullable();
            $table->time('process_start_time')->nullable();

            // --------------------
            // Processing Data
            // --------------------
            $table->string('processing_data')->nullable(); // EDTA, ACD, HEP
            $table->boolean('plasma_harvesting')->nullable();
            $table->json('sample_status')->nullable(); // NORM, HEM, CLOTTED
            $table->string('counting_method'); // Manual Count | Automated (required)
            $table->decimal('usable_blood_volume', 8, 2)->nullable(); // mL

            // --------------------
            // Manual Cell Counting
            // --------------------
            $table->json('manual_counts')->nullable();
            $table->time('manual_count_start_time')->nullable();
            $table->time('manual_count_stop_time')->nullable();
            $table->decimal('haemocytometer_factor', 10, 2)->default(10000);
            $table->decimal('pbmc_dilution_factor', 10, 2)->default(2);

            // --------------------
            // Calculated Outcomes (stored for audit/history)
            // --------------------
            $table->decimal('counting_resuspension', 10, 2)->nullable();
            $table->decimal('cell_count_concentration', 10, 2)->nullable();
            $table->decimal('total_cell_number', 15, 2)->nullable();
            $table->decimal('final_cps_resuspension_volume', 10, 3)->nullable();
            $table->decimal('viability_percent', 5, 2)->nullable();

            // --------------------
            // Automated Cell Count
            // --------------------
            $table->boolean('auto_system_clean_done')->nullable();
            $table->boolean('auto_qc_passed')->nullable();
            $table->decimal('auto_viability_percent', 5, 2)->nullable();
            $table->unsignedBigInteger('auto_total_viable_cells_original')->nullable();
            $table->unsignedBigInteger('auto_total_cells_original')->nullable();
            $table->unsignedInteger('auto_total_cryovials_frozen')->nullable();

            // --------------------
            // Stratacooler / Mr Frosty
            // --------------------
            $table->decimal('frosty_storage_temp', 5, 2)->nullable();
            $table->date('frosty_date')->nullable();
            $table->time('frosty_time')->nullable();
            $table->string('frosty_transfer')->nullable();

            // --------------------
            // LN2 Transfer
            // --------------------
            $table->string('ln2_transfer_first')->nullable();
            $table->string('ln2_transfer_last')->nullable();
            $table->dateTime('ln2_transfer_datetime')->nullable();
            $table->text('auto_comment')->nullable();

            $table->timestamps();

            // --------------------
            // Indexes
            // --------------------
            $table->index('study_choice');
            $table->index('ptid');
            $table->index(['ptid', 'visit']);
            $table->index('collection_date');
        });

        // --------------------
        // PBMC Reagents
        // --------------------
        Schema::create('pbmc_reagents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pbmc_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('name');
            $table->string('lot')->nullable();
            $table->date('expiry')->nullable();

            $table->timestamps();
            $table->index('pbmc_id');
        });

        // --------------------
        // PBMC Washes
        // --------------------
        Schema::create('pbmc_washes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pbmc_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->tinyInteger('wash_number');
            $table->time('start_time')->nullable();
            $table->time('stop_time')->nullable();
            $table->decimal('volume', 8, 2)->nullable(); // mL
            $table->string('centrifuge_id')->nullable();
            $table->integer('centrifuge_speed')->nullable(); // rpm

            $table->timestamps();

            $table->index('pbmc_id');
            $table->unique(['pbmc_id', 'wash_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pbmc_washes');
        Schema::dropIfExists('pbmc_reagents');
        Schema::dropIfExists('pbmcs');
    }
};
