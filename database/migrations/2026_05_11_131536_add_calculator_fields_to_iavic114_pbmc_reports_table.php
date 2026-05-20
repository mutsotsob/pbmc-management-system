<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iavic114_pbmc_reports', function (Blueprint $table) {
            $table->string('sample_tube_type', 50)->nullable()->after('sample_condition');
            $table->string('plasma_harvesting', 50)->nullable()->after('sample_tube_type');
            $table->string('counting_method', 50)->nullable()->after('plasma_harvesting');
            $table->decimal('dilution_factor', 5, 1)->default(10)->after('counting_method');
            $table->decimal('final_cps_volume_ml', 8, 2)->nullable()->after('cell_yield_per_ml_blood');
        });
    }

    public function down(): void
    {
        Schema::table('iavic114_pbmc_reports', function (Blueprint $table) {
            $table->dropColumn([
                'sample_tube_type',
                'plasma_harvesting',
                'counting_method',
                'dilution_factor',
                'final_cps_volume_ml',
            ]);
        });
    }
};
