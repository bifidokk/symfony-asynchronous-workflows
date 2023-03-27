<?php

namespace App\Service\Workflow\Order\Transition;

use App\Service\Workflow\Order\OrderCreate;
use Symfony\Component\Workflow\WorkflowInterface;

class ConfirmOrder
{
    public function __construct(
        private WorkflowInterface $orderCreateStateMachine,
    ) {
    }

    public function handle(OrderCreate $orderCreate): void
    {
        // make some confirmation here
        // ....
        $orderCreate->setCurrentState('confirmed');

        $this->orderCreateStateMachine->apply($orderCreate, 'complete_order');
    }
}
