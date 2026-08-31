<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AuditEvent extends Model {
    public const UPDATED_AT = null;
    protected $fillable = ['user_id','event','subject_type','subject_id','before_values','after_values','ip_address','user_agent'];
    protected function casts(): array { return ['before_values' => 'array', 'after_values' => 'array']; }
}
