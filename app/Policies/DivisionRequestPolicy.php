<?php

namespace App\Policies;

use App\Models\DivisionRequest;
use App\Models\User;

class DivisionRequestPolicy
{
    public function respond(User $user, DivisionRequest $divisionRequest): bool
    {
        return (int) $user->id === (int) $divisionRequest->to_user_id;
    }
}