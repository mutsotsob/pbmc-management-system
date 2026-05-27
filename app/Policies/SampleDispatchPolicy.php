<?php

namespace App\Policies;

use App\Models\SampleDispatch;
use App\Models\User;

class SampleDispatchPolicy
{
    public function create(User $user): bool
    {
        return $user->hasFullSystemAccess() || $user->isDepartment('Clinical Operations');
    }

    public function receive(User $user, SampleDispatch $dispatch): bool
    {
        if (!$this->isLaboratoryActor($user)) {
            return false;
        }

        return $dispatch->status === 'dispatched';
    }

    public function reject(User $user, SampleDispatch $dispatch): bool
    {
        if (!$this->isLaboratoryActor($user)) {
            return false;
        }

        return $dispatch->status === 'dispatched';
    }

    public function process(User $user, SampleDispatch $dispatch): bool
    {
        if (!$this->isLaboratoryActor($user)) {
            return false;
        }

        return $dispatch->status === 'received' && $dispatch->condition_on_arrival !== 'Rejected';
    }

    private function isLaboratoryActor(User $user): bool
    {
        return $user->hasFullAccessDepartment() || str_contains(strtolower((string) $user->department), 'lab');
    }
}
