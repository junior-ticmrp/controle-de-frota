<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Person extends Model
{
 use HasFactory;
 protected $fillable=['full_name','role','sector','document','phone','email','active','legacy_name'];
 protected function casts(): array { return ['active'=>'boolean']; }
 public function requestedFuelRequests(): HasMany { return $this->hasMany(FuelRequest::class,'requester_person_id'); }
 public function drivenFuelRequests(): HasMany { return $this->hasMany(FuelRequest::class,'driver_person_id'); }
 public function fuelings(): HasMany { return $this->hasMany(Fueling::class,'driver_person_id'); }
 public function vehicleResponsibilities(): HasMany { return $this->hasMany(VehicleResponsibility::class); }
}
