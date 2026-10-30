<?php

namespace App\Domain\Identity;

enum Role: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case User = 'user';

    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::cases(),
            self::Manager => [Permission::ViewOwnAccount, Permission::WriteContent],
            self::User => [Permission::ViewOwnAccount],
        };
    }
}
