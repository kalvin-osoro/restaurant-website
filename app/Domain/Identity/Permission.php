<?php

namespace App\Domain\Identity;

enum Permission: string
{
    case ViewOwnAccount = 'account.view';
    case WriteContent = 'content.write';
    case AssignRoles = 'users.assign-roles';
}
