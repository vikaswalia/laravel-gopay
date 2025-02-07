<?php

namespace PavelZanek\LaravelGoPaySDK\Support;

use GoPay\Http\Log\Logger as DefLogger;
use GoPay\Http\Request;
use GoPay\Http\Response;

class Logger implements DefLogger
{
    public function log(string $message)
    {
        \GoPaySDK::log($message);
    }

    public function logHttpCommunication(Request $request, Response $response)
    {
        \GoPaySDK::logHttpCommunication($request, $response);
    }
}