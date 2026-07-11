<?php

namespace Modules\TitanZero\Policies;

use App\Models\User;

class TitanZeroPolicy
{
    public function use(User $user): bool
    {
        return method_exists($user, 'hasPermission') ? $user->hasPermission('titanzero.use') : true;
    }

    public function admin(User $user): bool
    {
        return method_exists($user, 'hasPermission') ? $user->hasPermission('titanzero.admin') : false;
    }

    public function apply(User $user): bool
    {
        return method_exists($user, 'hasPermission') ? $user->hasPermission('titanzero.apply') : false;
    }
}
