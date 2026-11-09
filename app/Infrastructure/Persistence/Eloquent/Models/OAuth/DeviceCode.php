<?php

namespace App\Infrastructure\Persistence\Eloquent\Models\OAuth;

class DeviceCode extends \Laravel\Passport\DeviceCode
{
    protected $table = 'tbl_oauth_device_codes';
}
