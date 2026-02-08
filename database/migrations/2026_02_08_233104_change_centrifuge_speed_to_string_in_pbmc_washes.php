<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pbmc_washes', function (Blueprint $table) {
            $table->string('centrifuge_speed')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pbmc_washes', function (Blueprint $table) {
            // revert only if you REALLY need to
            $table->integer('centrifuge_speed')->change();
        });
    }
};
