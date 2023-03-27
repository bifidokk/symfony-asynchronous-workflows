<?php

namespace App\Service\Workflow\Order\Transition;

use App\Service\Workflow\Order\OrderCreate;
use Symfony\Component\Workflow\WorkflowInterface;

class VerifyOrder
{
    public function __construct(
        private WorkflowInterface $orderCreateStateMachine,
    ) {
    }

    public function handle(OrderCreate $orderCreate): void
    {
        // make some verification here
        // ....
        $orderCreate->setCurrentState('verified');

        $this->orderCreateStateMachine->apply($orderCreate, 'confirm_order');
    }
}
