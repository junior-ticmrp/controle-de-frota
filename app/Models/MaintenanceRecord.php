<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = ['vehicle_id', 'service_type', 'performed_at', 'odometer', 'cost', 'next_maintenance_at', 'next_maintenance_odometer', 'provider', 'notes', 'created_by_user_id'];

    protected function casts(): array
    {
        return ['performed_at' => 'datetime', 'next_maintenance_at' => 'datetime', 'odometer' => 'integer', 'next_maintenance_odometer' => 'integer', 'cost' => 'decimal:2'];
    }

    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
