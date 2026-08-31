<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuVisibility extends Model
{
    protected $fillable = ['menu_key', 'role', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
