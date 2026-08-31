<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('auth_source', 16)->default('ldap')->index()->after('domain');
            $table->string('role', 16)->default('user')->index()->after('auth_source');
            $table->boolean('is_active')->default(true)->index()->after('role');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_auth_source_index');
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_is_active_index');
            $table->dropColumn(['auth_source', 'role', 'is_active', 'last_login_at']);
        });
    }
};
