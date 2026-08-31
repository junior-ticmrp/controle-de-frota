<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('fuel_requests', 'responsible_sector')) {
            Schema::table('fuel_requests', function (Blueprint $table): void {
                $table->string('responsible_sector', 120)->nullable()->after('requester_person_id');
            });
        }
    }
    public function down(): void {
        if (Schema::hasColumn('fuel_requests', 'responsible_sector')) {
            Schema::table('fuel_requests', function (Blueprint $table): void {
                $table->dropColumn('responsible_sector');
            });
        }
    }
};
