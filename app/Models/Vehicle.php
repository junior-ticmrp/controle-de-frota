<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Vehicle extends Model
{
 use HasFactory;
 protected $fillable=['plate','model','brand','manufacture_year','fuel_type_id','current_odometer','status','maintenance_interval_km','maintenance_interval_days','legacy_plate'];
 protected function casts(): array { return ['manufacture_year'=>'integer','current_odometer'=>'integer','maintenance_interval_km'=>'integer','maintenance_interval_days'=>'integer']; }
 public function fuelType(): BelongsTo { return $this->belongsTo(FuelType::class); }
 public function fuelRequests(): HasMany { return $this->hasMany(FuelRequest::class); }
 public function fuelings(): HasMany { return $this->hasMany(Fueling::class); }
 public function maintenanceRecords(): HasMany { return $this->hasMany(MaintenanceRecord::class); }
 public function responsibilities(): HasMany { return $this->hasMany(VehicleResponsibility::class); }
 public function activeResponsibilities(): HasMany { return $this->hasMany(VehicleResponsibility::class)->whereNull('ended_at')->orderBy('responsibility_type')->orderBy('sector'); }
 public function isOperationallyAvailable(): bool { return $this->status==='active'; }
}
