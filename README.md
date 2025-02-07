# GoPay SDK for Laravel


- [GoPay SDK for Laravel](#gopay-sdk-for-laravel)
  - [Requirements](#requirements)
  - [Installation](#installation)
    - [Step 1: Install package](#step-1-install-package)
    - [Step 2: Configuration](#step-2-configuration)
  - [Features](#features)
    - [Languages](#languages)
    - [Scopes](#scopes)
    - [Events](#events)
  - [Usage/Examples](#usageexamples)
    - [Facade GoPaySDK](#facade-gopaysdk)
    - [Check Payment State](#check-payment-state)
  - [License](#license)

## Requirements

The GoPay SDK for Laravel package requires PHP 8.0+, Laravel 8+.

## Installation

### Step 1: Install package

Add the package in your composer.json by executing the command:

```
composer require pavelzanek/laravel-gopay-sdk
```

This command installs the package into the vendor/ directory.

### Step 2: Configuration

You can initialise config file by running command:

```
php artisan vendor:publish --provider="PavelZanek\LaravelGoPaySDK\Providers\GoPayServiceProvider" --tag="config"
```

Next, you can see newly created file located in `config` folder - `gopay.php`. 

By default, the config file looks like this

```php
<?php

return [
    'goid' => env('GOPAY_ID'),
    'clientId' => env('GOPAY_CLIENT_ID'),
    'clientSecret' => env('GOPAY_CLIENT_SECRET'),
    'defaultScope' => env('GOPAY_DEFAULT_SCOPE', 'ALL'),
    'gatewayUrl' => env('GOPAY_PRODUCTION_ENV', true) ? 
        'https://gate.gopay.cz/' : 'https://gw.sandbox.gopay.com/',
    'languages' => [ 
        'en' => 'ENGLISH',
        'sk' => 'SLOVAK',
        'cs' => 'CZECH'
    ],
    'timeout' => 30
];
```

Basic variables can be set in .env file.

## Features

### Languages

You can set up payment gateway interface language when you creating new payment.

Via GoPay Definition
```php
\GoPaySDK::lang(GoPay\Definition\Languages::CZECH)
```

Via Language Code
```php
\GoPaySDK::lang('cs')
```

Via String
```php
\GoPaySDK::lang('CZECH')
```

### Scopes

Via GoPay Definition
```php
\GoPaySDK::scope(GoPay\Definition\TokenScope::CREATE_PAYMENT)
```

Via String
```php
\GoPay::scope('CREATE_PAYMENT')
```

### Events

|  **Name**      |                     **Class**                    |
|:--------------:|:------------------------------------------------:|
| PaymentCreated | PavelZanek\LaravelGoPaySDK\Events\PaymentCreated |

```php
Event::listen(\PavelZanek\LaravelGoPaySDK\Events\PaymentCreated::class, function ($event) {
    dd($event->payment);
});
```

## Usage/Examples

### Facade GoPaySDK

For example you can use facade `GoPaySDK` in a Controller to create standard payment:

```php
<?php

namespace App\Http\Controllers\Support\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\StoreOrderRequest;
use GoPaySDK;
use GoPay\Definition\Payment\Currency;
use GoPay\Definition\Payment\PaymentInstrument;

class OrdersController extends Controller
{
  public function storeOrder(StoreOrderRequest $request, Order $order): RedirectResponse
  {

    // your code

    GoPaySDk::log(function($request, $response){
      \Log::info("{$request->method} {$request->url} -> {$response->statusCode}");
    });

    // You can use https://doc.gopay.com/#payment-creation
    $response = GoPaySDK::lang(strtoupper($order->locale))->scope('CREATE_PAYMENT')->createPayment([ 
      'payer' => [
        'default_payment_instrument' => PaymentInstrument::PAYMENT_CARD,
        'allowed_payment_instruments' => [PaymentInstrument::PAYMENT_CARD],
        'contact' => [
          'first_name' => $order->customer_name,
          'last_name' => $order->customer_surname,
          'email' => $order->customer_email,
          'phone_number' => $order->customer_telephone,
          'city' => $order->customer_city,
          'street' => $order->customer_street,
          'postal_code' => $order->customer_postal_code,
          'country_code' => $order->customer_state_code,
        ],
      ],
      'amount' => $order->total_price, // Total price (float, two decimal places, without separator - fe. 19900 will be 199,00)
      'currency' => Currency::CZECH_CROWNS,
      'order_number' => $order->id,
      'order_description' => 'Test',
      'items' => [
        // Only example, you have to do yourself
        [
          'name' => 'test',
          'amount' => 19900
        ],
      ],
      'additional_params' => [
        array(
          'name' => 'invoicenumber',
          'value' => $order->invoice_number
        )
      ],
      'callback' => [
        'return_url' => route('orders.show', $order),
        'notification_url' => route('gopayNotification')
      ]
    ]);

    //dd($response);

    if ($response->hasSucceed()) {
      // Logic when the response is successful
      // For example you can redirect users after some logic to process payment
      return redirect()->to($response->json['gw_url'], 301);
    }

    // rest of your code

  }
}
```

### Check Payment State

Here's a simple example of how you can get a payment status and how you can assign the status to an order.

[List of possible payment states](https://doc.gopay.com/#state)

```php

use GoPaySDK;
use App\Enums\Orders\OrderStatus;

// your code

$response = GoPaySDK::getStatus($order->gopay_payment_id);
if(isset($response->json['state'])){

    $status = match($response->json['state']) {
        'CREATED' => OrderStatus::Waiting,
        'PAID' => OrderStatus::Paid,
        'CANCELED' => OrderStatus::Canceled,
        'TIMEOUTED' => OrderStatus::Timeouted,
        'REFUNDED' => OrderStatus::Refunded,
        default => $response->json['state']
    };

    $order->update([
        'status' => $status,
    ]);
}

// rest of your code

```

## License

Copyright (c) Pavel Zaněk. MIT Licensed,
see [LICENSE](LICENSE.md) for details.