<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegacyUserLink extends Model
{
    use HasFactory;

    protected $fillable = ['legacy_login', 'linked_user_id', 'reviewed_by_user_id', 'reviewed_at'];

    protected function casts(): array { return ['reviewed_at' => 'datetime']; }

    public function linkedUser(): BelongsTo { return $this->belongsTo(User::class, 'linked_user_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_user_id'); }
}
