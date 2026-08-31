<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_visibilities', function (Blueprint $table): void {
            $table->id();
            $table->string('menu_key', 60);
            $table->string('role', 30);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['menu_key', 'role']);
            $table->index(['role', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_visibilities');
    }
};
