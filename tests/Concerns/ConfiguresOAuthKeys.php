<?php

namespace Tests\Concerns;

trait ConfiguresOAuthKeys
{
    protected function configureOAuthKeys(): void
    {
        static $keys;
        if ($keys === null) {
            $private = \phpseclib4\Crypt\RSA::createKey(2048);
            $keys = [$private->toString('PKCS8'), $private->getPublicKey()->toString('PKCS8')];
        }
        config(['passport.private_key' => $keys[0], 'passport.public_key' => $keys[1]]);
    }
}
