<?php

return [
    'user_group_dn' => env(
        'LDAP_FUEL_USER_GROUP_DN',
        'CN=G_Diretoria,OU=Diretoria,OU=Administracao,DC=cmrp,DC=pmrp,DC=com,DC=br',
    ),
    'supervisor_group_dn' => env(
        'LDAP_FUEL_SUPERVISOR_GROUP_DN',
        'CN=G_Frota_Supervisores,OU=Diretoria,OU=Administracao,DC=cmrp,DC=pmrp,DC=com,DC=br',
    ),
    'admin_group_dn' => env(
        'LDAP_FUEL_ADMIN_GROUP_DN',
        'CN=G_Informatica,OU=Informatica,OU=Administracao,DC=cmrp,DC=pmrp,DC=com,DC=br',
    ),
];
