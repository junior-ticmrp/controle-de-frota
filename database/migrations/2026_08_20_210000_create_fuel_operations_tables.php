<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 180);
            $table->enum('role', ['driver', 'council_member', 'staff', 'administrator'])->default('staff');
            $table->string('document', 32)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('email', 320)->nullable();
            $table->boolean('active')->default(true);
            $table->string('legacy_name', 180)->nullable();
            $table->timestamps();

            $table->index('role', 'people_role_idx');
            $table->index('active', 'people_active_idx');
        });

        Schema::create('fuel_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('abbreviation', 12);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique('name', 'fuel_types_name_uq');
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate', 10);
            $table->string('model', 120);
            $table->string('brand', 80)->nullable();
            $table->unsignedSmallInteger('manufacture_year')->nullable();
            $table->foreignId('fuel_type_id')->nullable()->constrained('fuel_types')->nullOnDelete();
            $table->unsignedBigInteger('current_odometer')->default(0);
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->unsignedInteger('maintenance_interval_km')->default(10000);
            $table->unsignedSmallInteger('maintenance_interval_days')->default(180);
            $table->string('legacy_plate', 32)->nullable();
            $table->timestamps();

            $table->unique('plate', 'vehicles_plate_uq');
            $table->index('status', 'vehicles_status_idx');
        });

        Schema::create('valorcomb', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_type_id')->constrained('fuel_types')->restrictOnDelete();
            $table->timestamp('effective_at');
            $table->decimal('valor_bruto', 12, 3);
            $table->decimal('desconto', 12, 4)->default(0);
            $table->decimal('valorcomb', 12, 3);
            $table->string('source', 80)->nullable();
            $table->unsignedBigInteger('legacy_codigo')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['fuel_type_id', 'effective_at'], 'valorcomb_fuel_effective_idx');
            $table->unique('legacy_codigo', 'valorcomb_legacy_codigo_uq');
        });

        Schema::create('fuel_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_number');
            $table->timestamp('requested_at');
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->foreignId('requester_person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('driver_person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('fuel_type_id')->constrained('fuel_types')->restrictOnDelete();
            $table->unsignedBigInteger('odometer')->default(0);
            $table->decimal('requested_liters', 12, 3)->nullable();
            $table->decimal('estimated_amount', 13, 2)->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'fulfilled', 'canceled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('legacy_codigo')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('request_number', 'fuel_requests_number_uq');
            $table->unique('legacy_codigo', 'fuel_requests_legacy_codigo_uq');
            $table->index('status', 'fuel_requests_status_idx');
            $table->index(['vehicle_id', 'requested_at'], 'fuel_requests_vehicle_date_idx');
        });

        Schema::create('fuelings', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fueling_at');
            $table->foreignId('request_id')->constrained('fuel_requests')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->foreignId('driver_person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('fuel_type_id')->constrained('fuel_types')->restrictOnDelete();
            $table->unsignedBigInteger('odometer');
            $table->decimal('liters', 12, 3);
            $table->decimal('unit_price', 12, 3);
            $table->decimal('total_amount', 13, 2);
            $table->string('invoice_number', 80)->nullable();
            $table->string('station_name', 160)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('legacy_codigo')->nullable();
            $table->string('legacy_source_key', 100)->nullable();
            $table->boolean('legacy_date_unreliable')->default(false);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('request_id', 'fuelings_request_idx');
            $table->unique('legacy_source_key', 'fuelings_legacy_source_key_uq');
            $table->index('legacy_codigo', 'fuelings_legacy_codigo_idx');
            $table->index(['vehicle_id', 'fueling_at'], 'fuelings_vehicle_date_idx');
            $table->index(['driver_person_id', 'fueling_at'], 'fuelings_driver_date_idx');
        });

        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->string('service_type', 120);
            $table->timestamp('performed_at');
            $table->unsignedBigInteger('odometer');
            $table->decimal('cost', 13, 2);
            $table->timestamp('next_maintenance_at')->nullable();
            $table->unsignedBigInteger('next_maintenance_odometer')->nullable();
            $table->string('provider', 160)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['vehicle_id', 'performed_at'], 'maintenance_vehicle_date_idx');
            $table->index('next_maintenance_at', 'maintenance_due_date_idx');
        });

        Schema::create('legacy_import_records', function (Blueprint $table) {
            $table->id();
            $table->enum('legacy_table', ['Vereadores', 'requisicoes', 'valorcomb', 'Usuario']);
            $table->string('legacy_key', 80);
            $table->string('target_table', 80)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->enum('status', ['imported', 'warning', 'skipped', 'failed'])->default('imported');
            $table->text('message')->nullable();
            $table->json('source_payload')->nullable();
            $table->timestamp('imported_at')->useCurrent();

            $table->unique(['legacy_table', 'legacy_key'], 'legacy_import_source_uq');
        });

        Schema::create('legacy_user_links', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_login', 120);
            $table->foreignId('linked_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique('legacy_login', 'legacy_user_links_login_uq');
            $table->index('linked_user_id', 'legacy_user_links_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_user_links');
        Schema::dropIfExists('legacy_import_records');
        Schema::dropIfExists('maintenance_records');
        Schema::dropIfExists('fuelings');
        Schema::dropIfExists('fuel_requests');
        Schema::dropIfExists('valorcomb');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('fuel_types');
        Schema::dropIfExists('people');
    }
};
