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
        Schema::table('pbmcs', function (Blueprint $table) {
            $table->boolean('imported_from_acrn')->default(false)->after('auto_comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pbmcs', function (Blueprint $table) {
            $table->dropColumn('imported_from_acrn');
        });
    }
};