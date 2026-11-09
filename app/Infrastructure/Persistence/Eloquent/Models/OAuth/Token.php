<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\OAuth;

class Token extends \Laravel\Passport\Token
{
    protected $table = 'tbl_oauth_access_tokens';
}
