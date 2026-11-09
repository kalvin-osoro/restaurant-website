<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\OAuth;

class Client extends \Laravel\Passport\Client
{
    protected $table = 'tbl_oauth_clients';
}
