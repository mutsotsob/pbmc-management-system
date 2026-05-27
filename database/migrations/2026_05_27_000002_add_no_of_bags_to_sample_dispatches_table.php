<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_dispatches', function (Blueprint $table) {
            $table->unsignedInteger('no_of_bags')->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('sample_dispatches', function (Blueprint $table) {
            $table->dropColumn('no_of_bags');
        });
    }
};
