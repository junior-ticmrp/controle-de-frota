<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class VehicleResponsibility extends Model
{
 use HasFactory;
 protected $fillable=['vehicle_id','responsibility_type','person_id','sector','started_at','ended_at','changed_by_user_id'];
 protected function casts(): array { return ['started_at'=>'datetime','ended_at'=>'datetime']; }
 public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
 public function person(): BelongsTo { return $this->belongsTo(Person::class); }
 public function changedBy(): BelongsTo { return $this->belongsTo(User::class,'changed_by_user_id'); }
}
