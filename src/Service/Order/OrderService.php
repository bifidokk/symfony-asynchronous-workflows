<?php

namespace App\Service\Order;


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


        //$this->orderCreateStateMachine->apply($orderCreate, 'verify_order');
    }
}
