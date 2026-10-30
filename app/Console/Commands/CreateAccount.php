<?php

namespace App\Console\Commands;

use App\Domain\Identity\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateAccount extends Command
{
    protected $signature = 'users:create {email} {--name=} {--role=user}';
    protected $description = 'Create an account with a validated role and an interactively supplied password';

    public function handle(): int
    {
        $data = ['email' => $this->argument('email'), 'name' => $this->option('name'),
            'role' => $this->option('role'), 'password' => $this->secret('Password (at least 12 characters)')];
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => ['required', 'string', 'min:12', 'max:128'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) { $this->error($error); }
            return self::FAILURE;
        }
        $record = $validator->validated();
        $user = new User(collect($record)->except('role')->all());
        $user->role = Role::from($record['role']);
        $user->save();
        $this->info('Account created.');
        return self::SUCCESS;
    }
}
