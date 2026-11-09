<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\OAuth;

class RefreshToken extends \Laravel\Passport\RefreshToken
{
    protected $table = 'tbl_oauth_refresh_tokens';
}
