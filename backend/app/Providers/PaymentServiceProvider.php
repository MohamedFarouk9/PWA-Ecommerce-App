<?php

namespace App\Providers;

use App\Services\Payments\PaymentGatewayFactory;
use App\Services\Payments\PaymentProcessor;
use App\Services\Payments\PaymentService;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register factory
        $this->app->singleton(PaymentGatewayFactory::class, function () {
            return new PaymentGatewayFactory();
        });

        // Register processor
        $this->app->singleton(PaymentProcessor::class, function ($app) {
            return new PaymentProcessor($app->make(PaymentGatewayFactory::class));
        });

        // Register payment service
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService(
                $app->make(PaymentProcessor::class),
                $app->make(PaymentGatewayFactory::class),
            );
        });
    }

    public function boot()
    {
        //
    }
}
