<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogger
{
    public static function log(?object $actor, string $action, ?string $targetType = null, $targetId = null, ?string $targetName = null, array $metadata = [], ?Request $request = null, ?string $description = null): void
    {
        $actorType = $actor ? class_basename($actor) : 'System';
        $actorId = $actor->id ?? null;
        $actorName = $actor ? trim(($actor->first_name ?? '') . ' ' . (($actor->middle_name ?? '') ? $actor->middle_name . ' ' : '') . ($actor->last_name ?? '')) : null;

        AuditLog::create([
            'actor_type'  => $actorType,
            'actor_id'    => $actorId,
            'actor_name'  => $actorName,
            'action'      => $action,
            'description' => $description,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'target_name' => $targetName,
            'metadata'    => $metadata,
            'ip_address'  => $request?->ip(),
            'user_agent'  => $request?->userAgent(),
        ]);
    }
}


