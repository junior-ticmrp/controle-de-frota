<?php

namespace App\Ldap;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use LdapRecord\Models\Model as LdapModel;
use LogicException;

class UserAttributeHandler
{
    public function handle(LdapModel $ldap, EloquentModel $database): void
    {
        $username = $ldap->getFirstAttribute('samaccountname');

        if (blank($username)) {
            throw new LogicException('O usuário do Active Directory não possui sAMAccountName.');
        }

        $database->name = $ldap->getFirstAttribute('displayname')
            ?: $ldap->getFirstAttribute('cn')
            ?: $username;
        $database->username = $username;
        $database->auth_source = 'ldap';

        if ($ldap->groups()->recursive()->exists(config('fuel-auth.admin_group_dn'))) {
            $database->role = 'admin';
        } elseif ($ldap->groups()->recursive()->exists(config('fuel-auth.supervisor_group_dn'))) {
            $database->role = 'supervisor';
        } else {
            $database->role = 'user';
        }

        $email = $ldap->getFirstAttribute('mail');
        if (filled($email)) {
            $database->email = mb_strtolower($email);
        }

        if (! $database->exists) {
            $database->is_active = true;
        }
    }
}
