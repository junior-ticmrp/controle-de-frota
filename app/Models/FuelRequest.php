<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class FuelRequest extends Model
{
 use HasFactory;
 protected $fillable=['request_number','requested_at','vehicle_id','requester_person_id','responsible_sector','driver_person_id','fuel_type_id','odometer','requested_liters','estimated_amount','status','notes','approved_by_user_id','approved_at','authorization_at','authorization_expires_at','rejection_reason','legacy_codigo','created_by_user_id'];
 protected function casts(): array { return ['requested_at'=>'datetime','approved_at'=>'datetime','authorization_at'=>'datetime','authorization_expires_at'=>'datetime','odometer'=>'integer','requested_liters'=>'decimal:3','estimated_amount'=>'decimal:2']; }
 public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); } public function requester(): BelongsTo { return $this->belongsTo(Person::class,'requester_person_id'); } public function driver(): BelongsTo { return $this->belongsTo(Person::class,'driver_person_id'); } public function fuelType(): BelongsTo { return $this->belongsTo(FuelType::class); } public function approver(): BelongsTo { return $this->belongsTo(User::class,'approved_by_user_id'); } public function creator(): BelongsTo { return $this->belongsTo(User::class,'created_by_user_id'); } public function fueling(): HasOne { return $this->hasOne(Fueling::class,'request_id'); }
 public function canBeApproved(): bool { return $this->status==='submitted'; }
 public function requiresAuthorization(): bool { return $this->requested_at!==null && $this->requested_at->gte(\Carbon\Carbon::create(2025,1,1,0,0,0)); }
 public function hasActiveAuthorization(): bool { return $this->authorization_at!==null && $this->authorization_expires_at!==null && $this->authorization_expires_at->isFuture(); }
 public function canBeFulfilled(): bool { return $this->status==='approved' && (!$this->requiresAuthorization() || $this->hasActiveAuthorization()); }
}
