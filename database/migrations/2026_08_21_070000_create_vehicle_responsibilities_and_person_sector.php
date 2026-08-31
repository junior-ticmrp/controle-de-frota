<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if(!Schema::hasColumn('people','sector')) Schema::table('people',function(Blueprint $table){$table->string('sector',120)->nullable()->after('role')->index();});
  if(!Schema::hasTable('vehicle_responsibilities')) Schema::create('vehicle_responsibilities',function(Blueprint $table){$table->id();$table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();$table->enum('responsibility_type',['council_member','sector']);$table->foreignId('person_id')->nullable()->constrained('people')->nullOnDelete();$table->string('sector',120)->nullable();$table->dateTime('started_at');$table->dateTime('ended_at')->nullable();$table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();$table->timestamps();$table->index(['vehicle_id','ended_at'],'vehicle_responsibilities_active_idx');$table->index(['person_id','ended_at'],'vehicle_responsibilities_person_active_idx');});
 }
 public function down(): void { if(Schema::hasTable('vehicle_responsibilities')) Schema::drop('vehicle_responsibilities'); if(Schema::hasColumn('people','sector')) Schema::table('people',function(Blueprint $table){$table->dropColumn('sector');}); }
};
