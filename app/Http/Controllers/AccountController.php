<?php

namespace App\Http\Controllers;

use App\Application\Identity\AssignRole;
use App\Domain\Identity\Role;
use App\Http\Requests\AssignRoleRequest;
use App\Models\User;
use Illuminate\Http\{JsonResponse, Request};

class AccountController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return $this->account($request->user());
    }

    public function assignRole(AssignRoleRequest $request, User $user, AssignRole $assign): JsonResponse
    {
        return $this->account($assign->execute($request->user(), $user, Role::from($request->validated('role'))));
    }

    private function account(User $user): JsonResponse
    {
        return response()->json(['data' => (new \App\Http\Resources\AccountResource($user))->resolve()]);
    }
}
