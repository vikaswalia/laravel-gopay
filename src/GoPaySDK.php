<?php

namespace PavelZanek\LaravelGoPaySDK;

use GoPay;
use PavelZanek\LaravelGoPaySDK\Events\PaymentCreated;
use PavelZanek\LaravelGoPaySDK\Support\LaravelTokenCache;
use PavelZanek\LaravelGoPaySDK\Support\Logger;

class GoPaySDK
{
    protected $gopay;

    protected $config = [];
    protected $services = [];
    protected $needReInit = false;

    protected $logsBefore = [];
    private $logClosure;

    public function __construct()
    {
        $this->config = [
            'goid' => config('gopay.goid'),
            'clientId' => config('gopay.clientId'),
            'clientSecret' => config('gopay.clientSecret'),
            'gatewayUrl' => config('gopay.gatewayUrl'),
            'timeout' => config('gopay.timeout')
        ];

        $fallback = config('app.fallback_locale');
        $language = isset(config('gopay.languages')[\App::getLocale()]) ? 
            config('gopay.languages')[\App::getLocale()] : config('gopay.languages')[$fallback];

        $this->config['language'] = defined($langConst = 'GoPay\Definition\Language::'.$language) ? 
            $this->config['language'] = constant($langConst) : $this->config['language'] = GoPay\Definition\Language::ENGLISH;

        $this->config['scope'] = defined($scopeConst = 'GoPay\Definition\TokenScope::'.config('gopay.defaultScope')) ?
            $this->config['scope'] = constant($scopeConst) : $this->config['scope'] = GoPay\Definition\TokenScope::CREATE_PAYMENT;

        $this->services['cache'] = new LaravelTokenCache();
        $this->services['logger'] = new Logger();

        $this->initGoPay();
    }

    protected function initGoPay()
    {
        $this->gopay = GoPay\Api::payments($this->config, $this->services);
        if($this->needReInit) $this->needReInit = false;
        return $this->gopay;
    }

    public function __call($name, $arguments)
    {
        if(method_exists($this, $name)){
            return $this->{$name}(...$arguments);
        } else if(method_exists($this->gopay, $name)){
            $gp = $this->needReInit ? $this->initGoPay() : $this->gopay;
            $methodResult = $gp->{$name}(...$arguments);

            match($name) {
                'createPayment' => event(new PaymentCreated($methodResult)),
                default => null
            };

            return $methodResult;
        }
        return null;
    }

    public function scope($scope)
    {
        $this->config['scope'] = defined($scopeConst = 'GoPay\Definition\TokenScope::'.$scope) ? 
            $this->config['scope'] = constant($scopeConst) : $this->config['scope'] = $scope;
        $this->needReInit = true;
        return $this;
    }

    public function lang($lang)
    {
        if(defined($langConst = 'GoPay\Definition\Language::'.$lang)){
            $this->config['language'] = constant($langConst);
        } else if(isset(config('gopay.languages')[$lang]) && defined($langConst = 'GoPay\Definition\Language::'.config('gopay.languages')[$lang])) {
            $this->config['language'] = constant($langConst);
        } else {
            $this->config['language'] = $lang;
        }
        $this->needReInit = true;
        return $this;
    }

    public function logHttpCommunication($request, $response)
    {
        if($this->logClosure == null) {
            $this->logsBefore[] = [$request, $response];
        } else {
            call_user_func($this->logClosure, $request, $response);
        }
    }

    public function log($closure)
    {
        $this->logClosure = $closure;
        foreach($this->logsBefore as $log)
        {
            call_user_func_array($this->logClosure, $log);
        }
        $this->logsBefore = [];
        return $this;
    }
}