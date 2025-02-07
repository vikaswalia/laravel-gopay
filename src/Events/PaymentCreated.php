<?php

namespace VikasWalia\LaravelGoPay\Events;

use GoPay\Http\Response;

class PaymentCreated
{
    public $payment;

    public function __construct(Response $paymentResponse)
    {
        $this->payment = $paymentResponse->json;
    }
}