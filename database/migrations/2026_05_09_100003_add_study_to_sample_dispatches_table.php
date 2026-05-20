<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_dispatches', function (Blueprint $table) {
            $table->string('study', 50)->default('C114')->after('sample_id');
        });
    }

    public function down(): void
    {
        Schema::table('sample_dispatches', function (Blueprint $table) {
            $table->dropColumn('study');
        });
    }
};
