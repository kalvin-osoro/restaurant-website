<?php

namespace App\Application\Identity;

use App\Domain\Identity\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AssignRole
{
    public function execute(User $actor, User $target, Role $role): User
    {
        return DB::transaction(function () use ($actor, $target, $role) {
            // Serialize role changes and recheck the actor against current database state.
            $users = User::whereIn('id', [$actor->id, $target->id])->orderBy('id')->lockForUpdate()->get();
            $actor = $users->firstWhere('id', $actor->id);
            abort_unless($actor?->isAdmin(), 403);
            $target = $users->firstWhere('id', $target->id);
            abort_if($target === null, 404);
            if ($actor->is($target) && $role !== Role::Admin) {
                throw ValidationException::withMessages(['role' => 'Administrators cannot demote their own account.']);
            }
            $target->role = $role;
            $target->save();
            return $target;
        });
    }
}
