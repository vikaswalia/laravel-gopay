<?php

namespace PavelZanek\LaravelGoPaySDK\Support;

use Cache;
use GoPay\Token\TokenCache;
use GoPay\Token\AccessToken;

class LaravelTokenCache implements TokenCache
{
    public function setAccessToken($client, AccessToken $accessToken)
    {
        Cache::put('gopay_token_'.$client, serialize($accessToken), config('gopay.timeout'));
    }

    public function getAccessToken($client)
    {
        $accessToken = Cache::get('gopay_token_'.$client);
        if (!is_null($accessToken)) {
            return unserialize($accessToken);
        }
        return null;
    }
}