<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fueling extends Model
{
    use HasFactory;

    protected $fillable = [
        'fueling_at', 'request_id', 'vehicle_id', 'driver_person_id', 'fuel_type_id', 'odometer', 'liters',
        'unit_price', 'total_amount', 'invoice_number', 'station_name', 'notes', 'legacy_codigo',
        'legacy_source_key', 'legacy_date_unreliable', 'created_by_user_id',
    ];

    protected function casts(): array
    {
        return ['fueling_at' => 'datetime', 'odometer' => 'integer', 'liters' => 'decimal:3', 'unit_price' => 'decimal:3', 'total_amount' => 'decimal:2', 'legacy_date_unreliable' => 'boolean'];
    }

    public function fuelRequest(): BelongsTo { return $this->belongsTo(FuelRequest::class, 'request_id'); }
    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
    public function driver(): BelongsTo { return $this->belongsTo(Person::class, 'driver_person_id'); }
    public function fuelType(): BelongsTo { return $this->belongsTo(FuelType::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
