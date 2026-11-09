<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\OAuth;

class AuthCode extends \Laravel\Passport\AuthCode
{
    protected $table = 'tbl_oauth_auth_codes';
}
