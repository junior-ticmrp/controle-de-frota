<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuelType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'abbreviation', 'active'];

    protected function casts(): array { return ['active' => 'boolean']; }

    public function vehicles(): HasMany { return $this->hasMany(Vehicle::class); }
    public function prices(): HasMany { return $this->hasMany(FuelPrice::class); }
    public function fuelRequests(): HasMany { return $this->hasMany(FuelRequest::class); }
    public function fuelings(): HasMany { return $this->hasMany(Fueling::class); }
}
