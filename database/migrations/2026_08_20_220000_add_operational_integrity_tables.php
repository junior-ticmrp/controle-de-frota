<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->string('name', 64)->primary();
            $table->unsignedBigInteger('last_value')->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        DB::table('document_sequences')->insertOrIgnore([
            'name' => 'fuel_request',
            'last_value' => 0,
            'updated_at' => now(),
        ]);

        Schema::table('fuelings', function (Blueprint $table) {
            $table->unique('request_id', 'fuelings_request_uq');
        });
    }

    public function down(): void
    {
        Schema::table('fuelings', function (Blueprint $table) {
            $table->dropUnique('fuelings_request_uq');
        });

        Schema::dropIfExists('document_sequences');
    }
};
