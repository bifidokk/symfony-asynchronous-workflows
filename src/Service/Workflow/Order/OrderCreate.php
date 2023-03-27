<?php

namespace App\Service\Workflow\Order;

use Symfony\Component\Uid\Uuid;

class OrderCreate
{
    public function __construct(
        private Uuid $orderId,
        private string $currentState,
    ) {
    }

    public function getOrderId(): Uuid
    {
        return $this->orderId;
    }

    public function setOrderId(Uuid $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getCurrentState(): string
    {
        return $this->currentState;
    }

    public function setCurrentState(string $currentState): void
    {
        $this->currentState = $currentState;
    }
}
