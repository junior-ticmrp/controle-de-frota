<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegacyImportRecord extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['legacy_table', 'legacy_key', 'target_table', 'target_id', 'status', 'message', 'source_payload', 'imported_at'];

    protected function casts(): array { return ['source_payload' => 'array', 'imported_at' => 'datetime']; }
}
