<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('year_levels', function (Blueprint $table) {
            $table->string('name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('year_levels', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
