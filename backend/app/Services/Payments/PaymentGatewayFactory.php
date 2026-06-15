<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    private array $gateways = [];

    public function __construct()
    {
        // Register available gateways
        $this->register('stripe', StripeGateway::class);
        $this->register('paymob', PaymobGateway::class);
        // $this->register('myfatoorah', MyFatoorahGateway::class);
    }

    /**
     * Register a gateway
     */
    public function register(string $name, string $className): self
    {
        $this->gateways[$name] = $className;
        return $this;
    }

    /**
     * Create a gateway instance
     */
    public function create(string $name): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$name])) {
            throw new InvalidArgumentException(
                "Payment gateway '{$name}' is not registered. " .
                "Available: " . implode(', ', array_keys($this->gateways))
            );
        }

        $className = $this->gateways[$name];
        return app($className);
    }

    /**
     * Get all registered gateways
     */
    public function all(): array
    {
        return array_keys($this->gateways);
    }
}
