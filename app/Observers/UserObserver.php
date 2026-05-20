<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\User;

class UserObserver
{
    private const EXCLUDED = ['password', 'remember_token', 'updated_at'];

    public function created(User $user): void
    {
        AuditLog::record('created', $user, [], $this->sanitize($user->getAttributes()));
    }

    public function updated(User $user): void
    {
        $dirty = collect($user->getDirty())
            ->except(self::EXCLUDED)
            ->keys();

        if ($dirty->isEmpty()) {
            return;
        }

        // Password changes get their own event — don't leak hashes
        if (collect($user->getDirty())->has('password')) {
            AuditLog::record('password_changed', $user);
        }

        $old = collect($user->getOriginal())->only($dirty)->all();
        $new = collect($user->getAttributes())->only($dirty)->all();

        AuditLog::record('updated', $user, $old, $new);
    }

    public function deleted(User $user): void
    {
        AuditLog::record('deleted', $user, ['name' => $user->name, 'email' => $user->email]);
    }

    public function restored(User $user): void
    {
        AuditLog::record('restored', $user, [], ['name' => $user->name, 'email' => $user->email]);
    }

    private function sanitize(array $attrs): array
    {
        return collect($attrs)->except(self::EXCLUDED)->all();
    }
}
