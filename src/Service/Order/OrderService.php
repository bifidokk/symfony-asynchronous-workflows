<?php

namespace App\Service\Order;

use App\Service\Workflow\Order\OrderCreate;
use Symfony\Component\Uid\UuidV4;
use Symfony\Component\Workflow\WorkflowInterface;

class OrderService
{
    public function __construct(
        private WorkflowInterface $orderCreateStateMachine,
    ) {

    }

    public function createOrder(): void
    {
        $orderCreate = new OrderCreate(
            new UuidV4(),
            'starting'
        );

        $this->orderCreateStateMachine->apply($orderCreate, 'verify_order');
    }
}
