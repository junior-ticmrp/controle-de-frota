<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelPrice extends Model
{
    use HasFactory;

    protected $table = 'valorcomb';
    public $timestamps = false;

    protected $fillable = ['fuel_type_id', 'effective_at', 'valor_bruto', 'desconto', 'valorcomb', 'source', 'legacy_codigo', 'created_by_user_id'];

    protected function casts(): array
    {
        return ['effective_at' => 'datetime', 'created_at' => 'datetime', 'valor_bruto' => 'decimal:3', 'desconto' => 'decimal:4', 'valorcomb' => 'decimal:3'];
    }

    public function fuelType(): BelongsTo { return $this->belongsTo(FuelType::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
