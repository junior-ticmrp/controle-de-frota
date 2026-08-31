<?php

namespace App\Ldap\Rules;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\Model as LdapModel;

class AuthorizedFuelUser implements Rule
{
    public function passes(LdapModel $user, EloquentModel $model = null): bool
    {
        return $user->groups()->recursive()->contains([
            config('fuel-auth.user_group_dn'),
            config('fuel-auth.supervisor_group_dn'),
            config('fuel-auth.admin_group_dn'),
        ]);
    }
}
