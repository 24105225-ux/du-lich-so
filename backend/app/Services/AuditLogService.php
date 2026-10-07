<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    public function record(
        ?Request $request,
        string $action,
        string $entity,
        ?int $entityId = null,
        ?array $before = null,
        ?array $after = null,
        ?int $actorId = null
    ): void {
        AuditLog::query()->create([
            'actor_id' => $actorId ?? $request?->user()?->id,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'before_json' => $before ? json_encode($before, JSON_UNESCAPED_UNICODE) : null,
            'after_json' => $after ? json_encode($after, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $request?->ip(),
            'created_at' => now(),
        ]);
    }
}
