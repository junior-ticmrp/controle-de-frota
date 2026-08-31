<?php
namespace App\Services;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class MasterDataAuditService {
    public function record(User $actor, string $event, Model $subject, array $before, array $after, Request $request): void {
        AuditEvent::create([
            'user_id' => $actor->id, 'event' => $event, 'subject_type' => class_basename($subject), 'subject_id' => $subject->getKey(),
            'before_values' => $before ?: null, 'after_values' => $after ?: null,
            'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }
}
